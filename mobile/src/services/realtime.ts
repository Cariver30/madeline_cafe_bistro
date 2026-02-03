import Echo from 'laravel-echo';
import Pusher from 'pusher-js/react-native';
import {API_BASE_URL} from './api';

const PUSHER_KEY = 'b57dac5e1316abff45ba';
const PUSHER_CLUSTER = 'us2';

let echoInstance: Echo | null = null;
let echoToken: string | null = null;

const buildAuthEndpoint = () => `${API_BASE_URL}/broadcasting/auth`;

export const getEcho = (token: string) => {
  if (echoInstance && echoToken === token) {
    return echoInstance;
  }

  echoInstance = new Echo({
    broadcaster: 'pusher',
    key: PUSHER_KEY,
    cluster: PUSHER_CLUSTER,
    forceTLS: true,
    client: new Pusher(PUSHER_KEY, {
      cluster: PUSHER_CLUSTER,
      forceTLS: true,
      authEndpoint: buildAuthEndpoint(),
      auth: {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    }),
  });

  echoToken = token;

  return echoInstance;
};

export const disconnectEcho = () => {
  if (echoInstance) {
    echoInstance.disconnect();
    echoInstance = null;
    echoToken = null;
  }
};
