package com.madeline.bistro.tap

import com.facebook.react.bridge.Promise
import com.facebook.react.bridge.ReactApplicationContext
import com.facebook.react.bridge.ReactContextBaseJavaModule
import com.facebook.react.bridge.ReactMethod

class TapToPayModule(reactContext: ReactApplicationContext) :
  ReactContextBaseJavaModule(reactContext) {

  override fun getName(): String = "TapToPayModule"

  @ReactMethod
  fun isSupported(promise: Promise) {
    promise.resolve(false)
  }

  @ReactMethod
  fun initialize(promise: Promise) {
    promise.reject("tap_disabled", "Tap to Pay no esta habilitado en esta version.")
  }

  @ReactMethod
  fun registerDevice(tpn: String, merchantCode: String, authToken: String?, promise: Promise) {
    promise.reject("tap_disabled", "Tap to Pay no esta habilitado en esta version.")
  }

  @ReactMethod
  fun startSale(amount: Double, currency: String?, reference: String?, promise: Promise) {
    promise.reject("tap_disabled", "Tap to Pay no esta habilitado en esta version.")
  }
}
