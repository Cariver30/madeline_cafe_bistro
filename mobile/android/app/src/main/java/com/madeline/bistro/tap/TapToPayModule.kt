package com.cafenegroapp.tap

import android.app.Activity
import android.nfc.NfcAdapter
import com.cardtek.softpos.SoftPosService
import com.cardtek.softpos.constants.CurrencyCode
import com.cardtek.softpos.constants.TransactionType
import com.cardtek.softpos.interfaces.InitializeListener
import com.cardtek.softpos.interfaces.RegisterListener
import com.cardtek.softpos.interfaces.TransactionListener
import com.cardtek.softpos.results.SoftPosError
import com.cardtek.softpos.results.TransactionResult
import com.facebook.react.bridge.Promise
import com.facebook.react.bridge.ReactApplicationContext
import com.facebook.react.bridge.ReactContextBaseJavaModule
import com.facebook.react.bridge.ReactMethod
import com.facebook.react.bridge.WritableNativeMap

class TapToPayModule(private val reactContext: ReactApplicationContext) :
  ReactContextBaseJavaModule(reactContext) {

  private val softPosService: SoftPosService by lazy { SoftPosService(reactContext) }
  private var initializing = false
  private var pendingInitPromise: Promise? = null
  private var pendingRegisterPromise: Promise? = null
  private var pendingTransactionPromise: Promise? = null

  override fun getName(): String = "TapToPayModule"

  @ReactMethod
  fun isSupported(promise: Promise) {
    val hasNfc = NfcAdapter.getDefaultAdapter(reactContext) != null
    val sdkOk = android.os.Build.VERSION.SDK_INT >= 27
    promise.resolve(hasNfc && sdkOk)
  }

  @ReactMethod
  fun initialize(promise: Promise) {
    if (initializing) {
      promise.reject("tap_init_busy", "Inicializacion en proceso.")
      return
    }
    initializing = true
    pendingInitPromise = promise
    softPosService.initialize(object : InitializeListener {
      override fun onPOSReady() {
        initializing = false
        pendingInitPromise?.resolve(true)
        pendingInitPromise = null
      }

      override fun onRegisterNeed() {
        initializing = false
        pendingInitPromise?.resolve("register_required")
        pendingInitPromise = null
      }

      override fun onPermissionNeed(permissions: java.util.ArrayList<String>) {
        initializing = false
        val map = WritableNativeMap()
        map.putArray("permissions", com.facebook.react.bridge.Arguments.fromList(permissions))
        pendingInitPromise?.reject("tap_permissions", "Permisos requeridos.", map)
        pendingInitPromise = null
      }

      override fun onInitializeError(error: SoftPosError) {
        initializing = false
        pendingInitPromise?.reject(
          "tap_init_error",
          error.errorMessage ?: "Error inicializando Tap to Pay."
        )
        pendingInitPromise = null
      }
    })
  }

  @ReactMethod
  fun registerDevice(tpn: String, merchantCode: String, authToken: String?, promise: Promise) {
    if (pendingRegisterPromise != null) {
      promise.reject("tap_register_busy", "Registro en proceso.")
      return
    }
    pendingRegisterPromise = promise
    softPosService.register(tpn, merchantCode, authToken ?: "", object : RegisterListener {
      override fun onRegisterSuccess() {
        pendingRegisterPromise?.resolve(true)
        pendingRegisterPromise = null
      }

      override fun onRegisterError(error: SoftPosError) {
        pendingRegisterPromise?.reject(
          "tap_register_error",
          error.errorMessage ?: "Error registrando Tap to Pay."
        )
        pendingRegisterPromise = null
      }
    })
  }

  @ReactMethod
  fun startSale(amount: Double, currency: String?, reference: String?, promise: Promise) {
    if (pendingTransactionPromise != null) {
      promise.reject("tap_txn_busy", "Transaccion en proceso.")
      return
    }
    val activity: Activity = currentActivity ?: run {
      promise.reject("tap_no_activity", "No hay actividad activa.")
      return
    }

    val currencyEnum = try {
      CurrencyCode.valueOf((currency ?: "USD").uppercase())
    } catch (_: Throwable) {
      CurrencyCode.USD
    }
    val currencyCode = currencyEnum.code?.toIntOrNull() ?: 840
    val amountMinor = kotlin.math.round(amount * 100).toLong()

    pendingTransactionPromise = promise
    softPosService.startTransaction(
      amountMinor,
      TransactionType.SALE,
      currencyCode,
      reference ?: "",
      activity,
      object : TransactionListener {
        override fun onCompleted(result: TransactionResult) {
          val response = WritableNativeMap().apply {
            putString("transaction_id", result.transactionId)
            putString("masked_pan", result.maskedPan)
            putString("card_type", result.cardType?.name)
            putString("kernel_result", result.kernelResult?.name)
            putBoolean("emv_accepted", result.isEMVAccepted)
          }
          pendingTransactionPromise?.resolve(response)
          pendingTransactionPromise = null
        }

        override fun onStartTransactionError(error: SoftPosError) {
          pendingTransactionPromise?.reject(
            "tap_txn_error",
            error.errorMessage ?: "Error en transaccion."
          )
          pendingTransactionPromise = null
        }

        override fun onTimeout() {
          pendingTransactionPromise?.reject("tap_timeout", "Transaccion expirada.")
          pendingTransactionPromise = null
        }

        override fun onCardDetected() {}
        override fun onCardReadFail() {}
        override fun onCardReadSuccess() {}
        override fun onCardRemoved() {}
        override fun onCardStillExists() {}
        override fun onGoOnline(cardType: com.cardtek.softpos.constants.CardType) {}
        override fun onPlaySound(beepType: com.cardtek.softpos.kernel.BeepType) {}
      }
    )
  }
}
