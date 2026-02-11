import {PermissionsAndroid, Platform} from 'react-native';
import messaging from '@react-native-firebase/messaging';
import {registerDeviceToken} from './api';
import {User} from '../types';

let tokenRefreshUnsubscribe: (() => void) | null = null;

const shouldRegister = (user: User | null) =>
  !!user && (user.role === 'server' || user.role === 'manager');

export const initPushNotifications = async (
  apiToken: string | null,
  user: User | null,
) => {
  if (!apiToken || !shouldRegister(user)) {
    return;
  }

  if (Platform.OS === 'android' && Platform.Version >= 33) {
    const permission = await PermissionsAndroid.request(
      PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS,
    );
    if (permission !== PermissionsAndroid.RESULTS.GRANTED) {
      return;
    }
  }

  await messaging().registerDeviceForRemoteMessages();
  const token = await messaging().getToken();
  await registerDeviceToken(apiToken, token, Platform.OS);

  if (!tokenRefreshUnsubscribe) {
    tokenRefreshUnsubscribe = messaging().onTokenRefresh(async nextToken => {
      try {
        await registerDeviceToken(apiToken, nextToken, Platform.OS);
      } catch {
        // silencioso; se reintentará en el próximo login
      }
    });
  }
};

export const stopPushNotifications = () => {
  if (tokenRefreshUnsubscribe) {
    tokenRefreshUnsubscribe();
    tokenRefreshUnsubscribe = null;
  }
};
