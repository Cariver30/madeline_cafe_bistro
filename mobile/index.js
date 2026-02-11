import 'react-native-gesture-handler';
import { AppRegistry } from 'react-native';
import messaging from '@react-native-firebase/messaging';
import App from './App';
import { name as appName } from './app.json';

messaging().setBackgroundMessageHandler(async () => {
  // No-op: system notification handles display for FCM notification payloads.
});

AppRegistry.registerComponent(appName, () => App);
