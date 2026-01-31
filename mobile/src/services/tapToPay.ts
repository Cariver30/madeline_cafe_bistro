import {NativeModules, Platform} from 'react-native';
import {getTapToPayConfig} from './api';
import type {TapToPayConfig} from '../types';

type TapToPayResult = {
  transaction_id?: string | null;
  masked_pan?: string | null;
  card_type?: string | null;
  kernel_result?: string | null;
  emv_accepted?: boolean;
};

type TapToPayModuleType = {
  isSupported(): Promise<boolean>;
  initialize(): Promise<boolean | 'register_required'>;
  registerDevice(
    tpn: string,
    merchantCode: string,
    authToken?: string | null,
  ): Promise<boolean>;
  startSale(
    amount: number,
    currency?: string | null,
    reference?: string | null,
  ): Promise<TapToPayResult>;
};

const {TapToPayModule} = NativeModules as {
  TapToPayModule?: TapToPayModuleType;
};

const ensureModule = () => {
  if (!TapToPayModule) {
    throw new Error('Tap to Pay no esta disponible en este dispositivo.');
  }
  return TapToPayModule;
};

const ensureRegistration = async (token: string) => {
  const config: TapToPayConfig = await getTapToPayConfig(token);
  if (!config?.tpn || !config?.merchant_code) {
    throw new Error('Tap to Pay no esta configurado en el sistema.');
  }
  await ensureModule().registerDevice(
    config.tpn,
    config.merchant_code,
    config.auth_token ?? null,
  );
};

export const startTapToPaySale = async ({
  token,
  amount,
  reference,
  currency = 'USD',
}: {
  token: string;
  amount: number;
  reference?: string;
  currency?: string;
}) => {
  if (Platform.OS !== 'android') {
    throw new Error('Tap to Pay solo esta disponible en Android.');
  }

  const module = ensureModule();
  const supported = await module.isSupported();
  if (!supported) {
    throw new Error('Este dispositivo no soporta Tap to Pay.');
  }

  const initResult = await module.initialize();
  if (initResult === 'register_required') {
    await ensureRegistration(token);
    await module.initialize();
  }

  return module.startSale(amount, currency, reference ?? null);
};
