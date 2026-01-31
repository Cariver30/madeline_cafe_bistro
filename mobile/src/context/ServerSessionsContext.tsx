import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
} from 'react';
import {Vibration} from 'react-native';
import {useAuth} from './AuthContext';
import {
  cancelOrder as cancelOrderRequest,
  closeTableSession,
  confirmOrder as confirmOrderRequest,
  getActiveTableSessions,
  renewTableSession,
} from '../services/api';
import {disconnectEcho, getEcho} from '../services/realtime';
import {TableOrder, TableSession} from '../types';
import {getKitchenSummary} from '../utils/serverOrderHelpers';

export type PendingOrderEntry = {
  order: TableOrder;
  session: TableSession;
};

type ServerActionState = {
  confirmingOrderId: number | null;
  cancellingOrderId: number | null;
  renewingSessionId: number | null;
  closingSessionId: number | null;
};

type ServerSessionsContextValue = {
  sessions: TableSession[];
  loading: boolean;
  refreshing: boolean;
  error: string | null;
  newOrderNotice: number | null;
  kitchenNotice: {orderId: number; status: string} | null;
  pendingOrders: PendingOrderEntry[];
  pendingOrdersTotal: number;
  runningSessions: TableSession[];
  pendingSessions: TableSession[];
  idleSessions: TableSession[];
  actionState: ServerActionState;
  loadSessions: (showLoader?: boolean) => Promise<TableSession[] | null>;
  refresh: () => void;
  renewSession: (sessionId: number) => Promise<void>;
  closeSession: (sessionId: number, tip?: number) => Promise<void>;
  confirmOrder: (orderId: number) => Promise<void>;
  cancelOrder: (orderId: number) => Promise<void>;
  getSessionById: (sessionId: number) => TableSession | undefined;
  getOrderById: (
    orderId: number,
  ) => {order: TableOrder; session: TableSession} | undefined;
};

const ServerSessionsContext = createContext<
  ServerSessionsContextValue | undefined
>(undefined);

export const ServerSessionsProvider = ({
  children,
}: {
  children: React.ReactNode;
}) => {
  const {token, user} = useAuth();
  const [sessions, setSessions] = useState<TableSession[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [newOrderNotice, setNewOrderNotice] = useState<number | null>(null);
  const [kitchenNotice, setKitchenNotice] = useState<{
    orderId: number;
    status: string;
  } | null>(null);
  const [actionState, setActionState] = useState<ServerActionState>({
    confirmingOrderId: null,
    cancellingOrderId: null,
    renewingSessionId: null,
    closingSessionId: null,
  });
  const hasInitialOrders = useRef(false);
  const seenPendingOrders = useRef<Set<number>>(new Set());

  const trackNewOrders = useCallback((data: TableSession[]) => {
    const pendingIds = new Set<number>();
    data.forEach(session => {
      session.orders?.forEach(order => {
        const kitchenSummary = getKitchenSummary(order);
        if (kitchenSummary && kitchenSummary.status !== 'delivered') {
          pendingIds.add(order.id);
        }
      });
    });

    if (!hasInitialOrders.current) {
      hasInitialOrders.current = true;
      seenPendingOrders.current = pendingIds;
      return;
    }

    const newOnes = [...pendingIds].filter(
      id => !seenPendingOrders.current.has(id),
    );
    seenPendingOrders.current = pendingIds;

    if (newOnes.length) {
      setNewOrderNotice(newOnes.length);
      Vibration.vibrate([0, 500, 200, 500]);
      setTimeout(() => setNewOrderNotice(null), 6000);
    }
  }, []);

  const loadSessions = useCallback(
    async (showLoader = true) => {
      if (!token) {
        return null;
      }
      try {
        if (showLoader) {
          setLoading(true);
        }
        const data = await getActiveTableSessions(token);
        setSessions(data);
        trackNewOrders(data);
        setError(null);
        return data;
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo cargar.');
        return null;
      } finally {
        if (showLoader) {
          setLoading(false);
        }
        setRefreshing(false);
      }
    },
    [token, trackNewOrders],
  );

  useEffect(() => {
    if (!token) {
      setSessions([]);
      setLoading(false);
      setActionState({
        confirmingOrderId: null,
        cancellingOrderId: null,
        renewingSessionId: null,
        closingSessionId: null,
      });
      hasInitialOrders.current = false;
      seenPendingOrders.current = new Set();
      setKitchenNotice(null);
      disconnectEcho();
      return;
    }

    loadSessions();
    const interval = setInterval(() => {
      if (actionState.confirmingOrderId || actionState.cancellingOrderId) {
        return;
      }
      loadSessions(false);
    }, 12000);
    return () => clearInterval(interval);
  }, [token, loadSessions, actionState.confirmingOrderId, actionState.cancellingOrderId]);

  useEffect(() => {
    if (!token || !user) {
      return;
    }
    if (user.role !== 'server' && user.role !== 'manager') {
      return;
    }

    const echo = getEcho(token);
    const channelName =
      user.role === 'manager'
        ? 'private-manager.orders'
        : `private-server.${user.id}`;
    const channel = echo.private(channelName);

    channel.listen(
      '.KitchenItemStatusUpdated',
      (event: {order_id: number; status: string}) => {
        if (event?.status === 'ready') {
          setKitchenNotice({orderId: event.order_id, status: event.status});
          Vibration.vibrate([0, 300, 150, 300]);
          setTimeout(() => setKitchenNotice(null), 6000);
        }
        loadSessions(false);
      },
    );

    return () => {
      echo.leaveChannel(channelName);
    };
  }, [token, user, loadSessions]);

  const refresh = useCallback(() => {
    setRefreshing(true);
    loadSessions(false);
  }, [loadSessions]);

  const renewSession = useCallback(
    async (sessionId: number) => {
      if (!token) {
        return;
      }
      setActionState(prev => ({...prev, renewingSessionId: sessionId}));
      try {
        await renewTableSession(token, sessionId);
        await loadSessions(false);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo renovar.');
      } finally {
        setActionState(prev => ({...prev, renewingSessionId: null}));
      }
    },
    [token, loadSessions],
  );

  const closeSession = useCallback(
    async (sessionId: number, tip?: number) => {
      if (!token) {
        return;
      }
      setActionState(prev => ({...prev, closingSessionId: sessionId}));
      try {
        await closeTableSession(token, sessionId, tip);
        await loadSessions(false);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo cerrar.');
      } finally {
        setActionState(prev => ({...prev, closingSessionId: null}));
      }
    },
    [token, loadSessions],
  );

  const confirmOrder = useCallback(
    async (orderId: number) => {
      if (!token) {
        return;
      }
      setActionState(prev => ({...prev, confirmingOrderId: orderId}));
      setSessions(prev =>
        prev.map(session => ({
          ...session,
          orders: session.orders?.map(order =>
            order.id === orderId ? {...order, status: 'confirmed'} : order,
          ),
        })),
      );
      try {
        await confirmOrderRequest(token, orderId);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo actualizar.');
        await loadSessions(false);
      } finally {
        setActionState(prev => ({...prev, confirmingOrderId: null}));
      }
    },
    [token, loadSessions],
  );

  const cancelOrder = useCallback(
    async (orderId: number) => {
      if (!token) {
        return;
      }
      setActionState(prev => ({...prev, cancellingOrderId: orderId}));
      setSessions(prev =>
        prev.map(session => ({
          ...session,
          orders: session.orders?.map(order =>
            order.id === orderId ? {...order, status: 'cancelled'} : order,
          ),
        })),
      );
      try {
        await cancelOrderRequest(token, orderId);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo actualizar.');
        await loadSessions(false);
      } finally {
        setActionState(prev => ({...prev, cancellingOrderId: null}));
      }
    },
    [token, loadSessions],
  );

  const pendingOrders = useMemo(() => {
    const list: PendingOrderEntry[] = [];
    sessions.forEach(session => {
      session.orders?.forEach(order => {
        const kitchenSummary = getKitchenSummary(order);
        if (kitchenSummary && kitchenSummary.status !== 'delivered') {
          list.push({order, session});
        }
      });
    });
    return list.sort((a, b) => {
      const dateA = a.order.created_at
        ? new Date(a.order.created_at).getTime()
        : 0;
      const dateB = b.order.created_at
        ? new Date(b.order.created_at).getTime()
        : 0;
      return dateB - dateA;
    });
  }, [sessions]);

  const runningSessions = useMemo(
    () => sessions.filter(session => session.status !== 'closed'),
    [sessions],
  );

  const pendingSessions = useMemo(
    () =>
      runningSessions.filter(
        session =>
          session.orders?.some(order => {
            const kitchenSummary = getKitchenSummary(order);
            return kitchenSummary && kitchenSummary.status !== 'delivered';
          }) ?? false,
      ),
    [runningSessions],
  );

  const idleSessions = useMemo(
    () =>
      runningSessions.filter(
        session =>
          !(
            session.orders?.some(order => {
              const kitchenSummary = getKitchenSummary(order);
              return kitchenSummary && kitchenSummary.status !== 'delivered';
            }) ?? false
          ),
      ),
    [runningSessions],
  );

  const getSessionById = useCallback(
    (sessionId: number) =>
      sessions.find(session => session.id === sessionId),
    [sessions],
  );

  const getOrderById = useCallback(
    (orderId: number) => {
      for (const session of sessions) {
        const order = session.orders?.find(candidate => candidate.id === orderId);
        if (order) {
          return {order, session};
        }
      }
      return undefined;
    },
    [sessions],
  );

  const value = useMemo<ServerSessionsContextValue>(
    () => ({
      sessions,
      loading,
      refreshing,
      error,
      newOrderNotice,
      kitchenNotice,
      pendingOrders,
      pendingOrdersTotal: pendingOrders.length,
      runningSessions,
      pendingSessions,
      idleSessions,
      actionState,
      loadSessions,
      refresh,
      renewSession,
      closeSession,
      confirmOrder,
      cancelOrder,
      getSessionById,
      getOrderById,
    }),
    [
      sessions,
      loading,
      refreshing,
      error,
      newOrderNotice,
      kitchenNotice,
      pendingOrders,
      runningSessions,
      pendingSessions,
      idleSessions,
      actionState,
      loadSessions,
      refresh,
      renewSession,
      closeSession,
      confirmOrder,
      cancelOrder,
      getSessionById,
      getOrderById,
    ],
  );

  return (
    <ServerSessionsContext.Provider value={value}>
      {children}
    </ServerSessionsContext.Provider>
  );
};

export const useServerSessions = () => {
  const context = useContext(ServerSessionsContext);
  if (!context) {
    throw new Error(
      'useServerSessions must be used within ServerSessionsProvider',
    );
  }
  return context;
};
