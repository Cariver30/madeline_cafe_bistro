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
import {
  cancelPosBatch,
  confirmPosBatch,
  createPosBatch,
  createPosPayment,
  createPosTicket,
  getPosTicket,
  getPosTickets,
  payPosTicket,
} from '../services/api';
import {disconnectEcho, getEcho} from '../services/realtime';
import {PosTicket, PosTicketPayload, ServerOrderItemPayload, TableOrder} from '../types';
import {useAuth} from './AuthContext';

export type PendingBatchEntry = {
  order: TableOrder;
  ticket: PosTicket;
};

type TicketActionState = {
  creatingTicket: boolean;
  addingItemsTicketId: number | null;
  payingTicketId: number | null;
  activeBatchId: number | null;
};

type PosTicketsContextValue = {
  tickets: PosTicket[];
  loading: boolean;
  refreshing: boolean;
  error: string | null;
  pendingBatches: PendingBatchEntry[];
  pendingTotal: number;
  actionState: TicketActionState;
  loadTickets: (showLoader?: boolean) => Promise<PosTicket[] | null>;
  refresh: () => void;
  createTicket: (payload: PosTicketPayload) => Promise<PosTicket | null>;
  addItems: (ticketId: number, items: ServerOrderItemPayload[]) => Promise<void>;
  addPayment: (ticketId: number, payload: {
    method: 'cash' | 'card' | 'ath' | 'split';
    split_mode: 'items' | 'amount';
    items?: number[];
    amount?: number;
    tip?: number;
    tip_percent?: number;
  }) => Promise<void>;
  confirmBatch: (batchId: number) => Promise<void>;
  cancelBatch: (batchId: number) => Promise<void>;
  payTicket: (
    ticketId: number,
    method: 'cash' | 'card' | 'ath' | 'split' | 'tap_to_pay',
    tip?: number,
  ) => Promise<void>;
  getTicketById: (ticketId: number) => PosTicket | undefined;
};

const PosTicketsContext = createContext<PosTicketsContextValue | undefined>(
  undefined,
);

export const PosTicketsProvider: React.FC<{children: React.ReactNode}> = ({
  children,
}) => {
  const {token, user} = useAuth();
  const [tickets, setTickets] = useState<PosTicket[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionState, setActionState] = useState<TicketActionState>({
    creatingTicket: false,
    addingItemsTicketId: null,
    payingTicketId: null,
    activeBatchId: null,
  });
  const hasInitial = useRef(false);
  const seenPending = useRef<Set<number>>(new Set());

  const trackNew = useCallback((data: PosTicket[]) => {
    const pendingIds = new Set<number>();
    data.forEach(ticket => {
      ticket.orders?.forEach(order => {
        if (order.status === 'pending') {
          pendingIds.add(order.id);
        }
      });
    });

    if (!hasInitial.current) {
      hasInitial.current = true;
      seenPending.current = pendingIds;
      return;
    }

    const newOnes = [...pendingIds].filter(id => !seenPending.current.has(id));
    seenPending.current = pendingIds;

    if (newOnes.length) {
      Vibration.vibrate([0, 200, 100, 200]);
    }
  }, []);

  const loadTickets = useCallback(
    async (showLoader = true) => {
      if (!token) {
        return null;
      }
      try {
        if (showLoader) {
          setLoading(true);
        }
        const data = await getPosTickets(token);
        setTickets(data);
        trackNew(data);
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
    [token, trackNew],
  );

  useEffect(() => {
    if (!token) {
      setTickets([]);
      setLoading(false);
      hasInitial.current = false;
      seenPending.current = new Set();
      disconnectEcho();
      return;
    }

    loadTickets();
    const interval = setInterval(() => {
      loadTickets(false);
    }, 12000);
    return () => clearInterval(interval);
  }, [token, loadTickets]);

  useEffect(() => {
    if (!token || !user) {
      return;
    }
    if (user.role !== 'pos' && user.role !== 'manager') {
      return;
    }

    const echo = getEcho(token);
    const channelName =
      user.role === 'manager'
        ? 'private-manager.orders'
        : `private-server.${user.id}`;
    const channel = echo.private(channelName);

    channel.listen('.KitchenItemStatusUpdated', () => {
      loadTickets(false);
    });

    return () => {
      echo.leaveChannel(channelName);
    };
  }, [token, user, loadTickets]);

  const refresh = useCallback(() => {
    setRefreshing(true);
    loadTickets(false);
  }, [loadTickets]);

  const createTicket = useCallback(
    async (payload: PosTicketPayload) => {
      if (!token) {
        return null;
      }
      setActionState(prev => ({...prev, creatingTicket: true}));
      try {
        const ticket = await createPosTicket(token, payload);
        setTickets(prev => [ticket, ...prev]);
        setError(null);
        return ticket;
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo crear.');
        return null;
      } finally {
        setActionState(prev => ({...prev, creatingTicket: false}));
      }
    },
    [token],
  );

  const addItems = useCallback(
    async (ticketId: number, items: ServerOrderItemPayload[]) => {
      if (!token) return;
      const optimisticOrderId = Date.now();
      const createdAt = new Date().toISOString();
      setActionState(prev => ({...prev, addingItemsTicketId: ticketId}));
      setError(null);
      setTickets(prev =>
        prev.map(ticket => {
          if (ticket.id !== ticketId) {
            return ticket;
          }

          const optimisticItems = items.map((item, index) => ({
            id: optimisticOrderId + index + 1,
            name: 'Producto',
            quantity: item.quantity,
            unit_price: 0,
            notes: null,
            category_scope: null,
            category_id: null,
            category_name: null,
            category_order: 0,
            extras: (item.extras ?? []).map(extra => ({
              id: extra.id,
              name: 'Extra',
              group_name: null,
              kind: null,
              price: 0,
              quantity: 1,
            })),
          }));

          return {
            ...ticket,
            orders: [
              ...(ticket.orders ?? []),
              {
                id: optimisticOrderId,
                status: 'pending',
                created_at: createdAt,
                confirmed_at: null,
                cancelled_at: null,
                items: optimisticItems,
              },
            ],
          };
        }),
      );
      try {
        await createPosBatch(token, ticketId, items);
        const updated = await getPosTicket(token, ticketId);
        setTickets(prev =>
          prev.map(ticket => (ticket.id === ticketId ? updated : ticket)),
        );
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo enviar.');
        await loadTickets(false);
        throw err;
      } finally {
        setActionState(prev => ({...prev, addingItemsTicketId: null}));
      }
    },
    [token, loadTickets],
  );

  const confirmBatch = useCallback(
    async (batchId: number) => {
      if (!token) return;
      setActionState(prev => ({...prev, activeBatchId: batchId}));
      try {
        await confirmPosBatch(token, batchId);
        await loadTickets(false);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo actualizar.');
      } finally {
        setActionState(prev => ({...prev, activeBatchId: null}));
      }
    },
    [token, loadTickets],
  );

  const cancelBatch = useCallback(
    async (batchId: number) => {
      if (!token) return;
      setActionState(prev => ({...prev, activeBatchId: batchId}));
      try {
        await cancelPosBatch(token, batchId);
        await loadTickets(false);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo actualizar.');
      } finally {
        setActionState(prev => ({...prev, activeBatchId: null}));
      }
    },
    [token, loadTickets],
  );

  const payTicket = useCallback(
    async (
      ticketId: number,
      method: 'cash' | 'card' | 'ath' | 'split' | 'tap_to_pay',
      tip?: number,
    ) => {
      if (!token) return;
      setActionState(prev => ({...prev, payingTicketId: ticketId}));
      try {
        await payPosTicket(token, ticketId, method, tip);
        await loadTickets(false);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo cobrar.');
      } finally {
        setActionState(prev => ({...prev, payingTicketId: null}));
      }
    },
    [token, loadTickets],
  );

  const addPayment = useCallback(
    async (
      ticketId: number,
      payload: {
        method: 'cash' | 'card' | 'ath' | 'split';
        split_mode: 'items' | 'amount';
        items?: number[];
        amount?: number;
        tip?: number;
        tip_percent?: number;
      },
    ) => {
      if (!token) return;
      setActionState(prev => ({...prev, payingTicketId: ticketId}));
      try {
        await createPosPayment(token, ticketId, payload);
        await loadTickets(false);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo cobrar.');
        throw err;
      } finally {
        setActionState(prev => ({...prev, payingTicketId: null}));
      }
    },
    [token, loadTickets],
  );

  const pendingBatches = useMemo(() => {
    const list: PendingBatchEntry[] = [];
    tickets.forEach(ticket => {
      ticket.orders?.forEach(order => {
        if (order.status === 'pending') {
          list.push({order, ticket});
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
  }, [tickets]);

  const getTicketById = useCallback(
    (ticketId: number) => tickets.find(ticket => ticket.id === ticketId),
    [tickets],
  );

  const value = useMemo<PosTicketsContextValue>(
    () => ({
      tickets,
      loading,
      refreshing,
      error,
      pendingBatches,
      pendingTotal: pendingBatches.length,
      actionState,
      loadTickets,
      refresh,
      createTicket,
      addItems,
      confirmBatch,
      cancelBatch,
      payTicket,
      addPayment,
      getTicketById,
    }),
    [
      tickets,
      loading,
      refreshing,
      error,
      pendingBatches,
      actionState,
      loadTickets,
      refresh,
      createTicket,
      addItems,
      confirmBatch,
      cancelBatch,
      payTicket,
      addPayment,
      getTicketById,
    ],
  );

  return (
    <PosTicketsContext.Provider value={value}>
      {children}
    </PosTicketsContext.Provider>
  );
};

export const usePosTickets = () => {
  const context = useContext(PosTicketsContext);
  if (!context) {
    throw new Error('usePosTickets must be used within PosTicketsProvider');
  }
  return context;
};
