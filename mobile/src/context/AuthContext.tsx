import React, {createContext, useContext, useEffect, useMemo, useState} from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {login as apiLogin, logout as apiLogout} from '../services/api';
import {User} from '../types';
import {
  initPushNotifications,
  stopPushNotifications,
} from '../services/pushNotifications';

type AuthContextType = {
  user: User | null;
  token: string | null;
  initializing: boolean;
  authLoading: boolean;
  authError: string | null;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  clearError: () => void;
};

const AuthContext = createContext<AuthContextType | undefined>(undefined);
const SESSION_KEY = '@madeline_bistro_mobile_session';

type StoredSession = {
  token: string;
  user: User;
};

export const AuthProvider: React.FC<{children: React.ReactNode}> = ({
  children,
}) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [initializing, setInitializing] = useState(true);
  const [authLoading, setAuthLoading] = useState(false);
  const [authError, setAuthError] = useState<string | null>(null);

  useEffect(() => {
    const hydrate = async () => {
      try {
        const stored = await AsyncStorage.getItem(SESSION_KEY);
        if (stored) {
          const session = JSON.parse(stored) as StoredSession;
          setToken(session.token);
          setUser(session.user);
        }
      } finally {
        setInitializing(false);
      }
    };

    hydrate();
  }, []);

  useEffect(() => {
    if (initializing) {
      return;
    }

    initPushNotifications(token, user).catch(() => {
      // silencioso: no bloquea el flujo si falla el registro de push
    });
  }, [token, user, initializing]);

  const persistSession = async (session: StoredSession | null) => {
    if (session) {
      await AsyncStorage.setItem(SESSION_KEY, JSON.stringify(session));
    } else {
      await AsyncStorage.removeItem(SESSION_KEY);
    }
  };

  const login = async (email: string, password: string) => {
    setAuthLoading(true);
    setAuthError(null);

    try {
      const data = await apiLogin(email, password);
      setToken(data.token);
      setUser(data.user);
      await persistSession({token: data.token, user: data.user});
    } catch (error) {
      const message =
        error instanceof Error ? error.message : 'Error al iniciar sesión.';
      setAuthError(message);
      throw error;
    } finally {
      setAuthLoading(false);
    }
  };

  const logout = async () => {
    if (token) {
      await apiLogout(token);
    }
    stopPushNotifications();
    setToken(null);
    setUser(null);
    await persistSession(null);
  };

  const value = useMemo(
    () => ({
      user,
      token,
      initializing,
      authLoading,
      authError,
      login,
      logout,
      clearError: () => setAuthError(null),
    }),
    [user, token, initializing, authLoading, authError],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe usarse dentro de AuthProvider');
  }
  return context;
};
