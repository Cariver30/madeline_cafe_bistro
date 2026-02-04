import {Vibration} from 'react-native';
import Sound from 'react-native-sound';

Sound.setCategory('Playback', true);

const HOST_SOUND = require('../assets/sounds/host_doorbell.wav');
const SERVER_SOUND = require('../assets/sounds/server_alert.wav');

let hostSound: Sound | null = null;
let serverSound: Sound | null = null;
let hostReady = false;
let serverReady = false;

const playSound = (
  source: number,
  sound: Sound | null,
  setSound: (next: Sound) => void,
  ready: boolean,
  setReady: (next: boolean) => void,
  pattern: number[],
  label: string,
) => {
  if (sound && ready) {
    sound.stop(() => sound.play());
    return;
  }

  if (!sound) {
    const created = new Sound(source, error => {
      if (error) {
        setReady(false);
        console.warn(`${label} sound failed to load`, error);
        Vibration.vibrate(pattern);
        return;
      }
      setReady(true);
      created.play();
    });
    setSound(created);
    return;
  }

  // Sound exists but didn't load correctly earlier.
  Vibration.vibrate(pattern);
};

export const preloadNotificationSounds = () => {
  if (!hostSound) {
    hostSound = new Sound(HOST_SOUND, error => {
      if (error) {
        hostReady = false;
        console.warn('Host sound failed to preload', error);
        return;
      }
      hostReady = true;
    });
  }

  if (!serverSound) {
    serverSound = new Sound(SERVER_SOUND, error => {
      if (error) {
        serverReady = false;
        console.warn('Server sound failed to preload', error);
        return;
      }
      serverReady = true;
    });
  }
};

export const playHostChime = () => {
  playSound(
    HOST_SOUND,
    hostSound,
    next => (hostSound = next),
    hostReady,
    next => (hostReady = next),
    [0, 200, 80, 200],
    'Host',
  );
};

export const playServerChime = () => {
  playSound(
    SERVER_SOUND,
    serverSound,
    next => (serverSound = next),
    serverReady,
    next => (serverReady = next),
    [0, 120, 80, 120, 80, 200],
    'Server',
  );
};
