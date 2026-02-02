import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {SafeAreaView} from 'react-native-safe-area-context';
import {useFocusEffect} from '@react-navigation/native';
import {KitchenStackParamList} from '../../navigation/kitchenTypes';
import {useAuth} from '../../context/AuthContext';
import {
  getKitchenOrders,
  updateKitchenOrderItemStatus,
} from '../../services/api';
import {getEcho} from '../../services/realtime';
import {KitchenOrder, KitchenOrderItemLabel} from '../../types';
import {formatTime} from '../../utils/serverOrderHelpers';

type StatusFilter = 'active' | 'ready' | 'delivered';

const STATUS_TABS: {key: StatusFilter; label: string}[] = [
  {key: 'active', label: 'Activas'},
  {key: 'ready', label: 'Listas'},
  {key: 'delivered', label: 'Entregadas'},
];

const STATUS_META: Record<
  string,
  {
    label: string;
    action?: string;
    next?: 'pending' | 'preparing' | 'ready' | 'delivered' | 'cancelled';
    color: string;
  }
> = {
  pending: {
    label: 'Pendiente',
    action: 'Preparar',
    next: 'preparing',
    color: '#fbbf24',
  },
  preparing: {
    label: 'Preparando',
    action: 'Listo',
    next: 'ready',
    color: '#38bdf8',
  },
  ready: {
    label: 'Listo',
    action: 'Entregar',
    next: 'delivered',
    color: '#22c55e',
  },
  delivered: {
    label: 'Entregado',
    color: '#94a3b8',
  },
  cancelled: {
    label: 'Cancelado',
    color: '#f87171',
  },
};

const KitchenOrdersScreen = ({
  route,
}: NativeStackScreenProps<KitchenStackParamList, 'KitchenOrders'>) => {
  const {token} = useAuth();
  const {areaId, labelId} = route.params ?? {};
  const [orders, setOrders] = useState<KitchenOrder[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [statusFilter, setStatusFilter] = useState<StatusFilter>('active');
  const [updating, setUpdating] = useState<Record<string, boolean>>({});
  const [tick, setTick] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setTick(prev => prev + 1);
    }, 30000);
    return () => clearInterval(interval);
  }, []);

  const statusParam = useMemo(() => {
    if (statusFilter === 'active') {
      return 'pending,preparing';
    }
    return statusFilter;
  }, [statusFilter]);

  const loadOrders = useCallback(
    async (showLoader = true) => {
      if (!token) {
        return;
      }
      try {
        if (showLoader) {
          setLoading(true);
        }
        const data = await getKitchenOrders(token, {
          areaId,
          labelId,
          status: statusParam,
        });
        setOrders(data);
        setError(null);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'No se pudo cargar.');
      } finally {
        if (showLoader) {
          setLoading(false);
        }
        setRefreshing(false);
      }
    },
    [token, areaId, labelId, statusParam],
  );

  useFocusEffect(
    useCallback(() => {
      let active = true;
      const run = async () => {
        if (active) {
          await loadOrders(true);
        }
      };
      run();

      const interval = setInterval(() => {
        if (active) {
          loadOrders(false);
        }
      }, 8000);

      return () => {
        active = false;
        clearInterval(interval);
      };
    }, [loadOrders]),
  );

  useEffect(() => {
    if (!token) {
      return;
    }
    if (!areaId && !labelId) {
      return;
    }

    const echo = getEcho(token);
    const channelName = labelId
      ? `private-kitchen.label.${labelId}`
      : `private-kitchen.area.${areaId}`;
    const channel = echo.private(channelName);

    channel.listen('.OrderItemsCreated', () => {
      loadOrders(false);
    });
    channel.listen('.KitchenItemStatusUpdated', () => {
      loadOrders(false);
    });

    return () => {
      echo.leaveChannel(channelName);
    };
  }, [token, areaId, labelId, loadOrders]);

  const handleRefresh = () => {
    setRefreshing(true);
    loadOrders(false);
  };

  const updateStatus = async (
    itemId: number,
    label: KitchenOrderItemLabel,
  ) => {
    if (!token) {
      return;
    }
    const meta = STATUS_META[label.status] ?? STATUS_META.pending;
    if (!meta.next) {
      return;
    }
    const key = `${itemId}-${label.id}`;
    setUpdating(prev => ({...prev, [key]: true}));
    setError(null);
    try {
      await updateKitchenOrderItemStatus(token, itemId, label.id, meta.next);
      await loadOrders(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo actualizar.');
    } finally {
      setUpdating(prev => {
        const next = {...prev};
        delete next[key];
        return next;
      });
    }
  };

  const totals = useMemo(() => {
    const items = orders.reduce((sum, order) => sum + order.items.length, 0);
    return {orders: orders.length, items};
  }, [orders]);

  const formatElapsed = (value?: string | null) => {
    if (!value) {
      return '';
    }
    const created = new Date(value).getTime();
    if (Number.isNaN(created)) {
      return '';
    }
    const minutes = Math.max(0, Math.floor((Date.now() - created) / 60000));
    if (minutes < 1) {
      return 'Hace <1 min';
    }
    if (minutes < 60) {
      return `Hace ${minutes} min`;
    }
    const hours = Math.floor(minutes / 60);
    const remaining = minutes % 60;
    return `Hace ${hours}h ${remaining}m`;
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.container}>
        <View style={styles.header}>
          <Text style={styles.heading}>Ordenes en cocina</Text>
          <Text style={styles.subheading}>
            {totals.orders} ordenes · {totals.items} items
          </Text>
        </View>

        <View style={styles.tabs}>
          {STATUS_TABS.map(tab => (
            <TouchableOpacity
              key={tab.key}
              style={[
                styles.tab,
                statusFilter === tab.key && styles.tabActive,
              ]}
              onPress={() => setStatusFilter(tab.key)}>
              <Text
                style={[
                  styles.tabText,
                  statusFilter === tab.key && styles.tabTextActive,
                ]}>
                {tab.label}
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        {error ? <Text style={styles.error}>{error}</Text> : null}

        {loading && !orders.length ? (
          <View style={styles.loader}>
            <ActivityIndicator color="#fbbf24" />
          </View>
        ) : (
          <FlatList
            data={orders}
            keyExtractor={item => `order-${item.order_id}`}
            refreshControl={
              <RefreshControl
                tintColor="#fbbf24"
                refreshing={refreshing}
                onRefresh={handleRefresh}
              />
            }
            ListEmptyComponent={
              <Text style={styles.emptyText}>No hay ordenes.</Text>
            }
            renderItem={({item}) => (
              <View style={styles.orderCard}>
                <View style={styles.orderHeader}>
                  <View>
                    <Text style={styles.orderTitle}>
                      Mesa {item.table_label ?? '—'}
                    </Text>
                    <Text style={styles.orderMeta}>
                      {item.guest_name ?? 'Cliente'} ·{' '}
                      {item.party_size ?? 0} personas
                    </Text>
                    <Text style={styles.orderMeta}>
                      {item.server_name ? `Mesero ${item.server_name}` : '—'}
                    </Text>
                  </View>
                  <View style={styles.orderTimeBlock}>
                    <Text style={styles.orderTime}>
                      {formatElapsed(item.created_at)}
                    </Text>
                    <Text style={styles.orderClock}>
                      {formatTime(item.created_at) ?? ''}
                    </Text>
                  </View>
                </View>

                {item.items.map(orderItem => (
                  <View key={orderItem.id} style={styles.itemCard}>
                    <Text style={styles.itemTitle}>
                      {orderItem.quantity}x {orderItem.name}
                    </Text>
                    {orderItem.notes ? (
                      <Text style={styles.itemNotes}>
                        Nota: {orderItem.notes}
                      </Text>
                    ) : null}
                    {orderItem.extras.length ? (
                      <Text style={styles.itemExtras}>
                        Extras:{' '}
                        {orderItem.extras
                          .map(extra => extra.name)
                          .join(', ')}
                      </Text>
                    ) : null}

                    {orderItem.labels.map(label => {
                      const meta =
                        STATUS_META[label.status] ?? STATUS_META.pending;
                      const key = `${orderItem.id}-${label.id}`;
                      const isUpdating = updating[key];
                      return (
                        <View key={key} style={styles.labelRow}>
                          <View
                            style={[
                              styles.statusBadge,
                              {borderColor: meta.color},
                            ]}>
                            <Text style={styles.statusBadgeText}>
                              {label.name} · {meta.label}
                            </Text>
                          </View>
                          {meta.action && meta.next ? (
                            <TouchableOpacity
                              style={[
                                styles.statusAction,
                                isUpdating && styles.statusActionDisabled,
                              ]}
                              disabled={isUpdating}
                              onPress={() => updateStatus(orderItem.id, label)}>
                              {isUpdating ? (
                                <ActivityIndicator color="#0f172a" />
                              ) : (
                                <Text style={styles.statusActionText}>
                                  {meta.action}
                                </Text>
                              )}
                            </TouchableOpacity>
                          ) : null}
                        </View>
                      );
                    })}
                  </View>
                ))}
              </View>
            )}
            contentContainerStyle={styles.listContent}
            extraData={tick}
          />
        )}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#020617',
  },
  container: {
    flex: 1,
    padding: 20,
  },
  header: {
    gap: 6,
    marginBottom: 14,
  },
  heading: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
  subheading: {
    color: '#94a3b8',
    fontSize: 13,
  },
  tabs: {
    flexDirection: 'row',
    backgroundColor: '#0f172a',
    borderRadius: 999,
    padding: 6,
    gap: 6,
    marginBottom: 12,
  },
  tab: {
    flex: 1,
    borderRadius: 999,
    paddingVertical: 8,
    alignItems: 'center',
  },
  tabActive: {
    backgroundColor: '#fbbf24',
  },
  tabText: {
    color: '#94a3b8',
    fontWeight: '600',
    fontSize: 12,
  },
  tabTextActive: {
    color: '#0f172a',
    fontWeight: '700',
  },
  error: {
    color: '#fb7185',
    marginBottom: 8,
  },
  loader: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  listContent: {
    paddingBottom: 40,
    gap: 16,
  },
  emptyText: {
    color: '#94a3b8',
    textAlign: 'center',
    marginTop: 30,
  },
  orderCard: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 12,
  },
  orderHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 12,
  },
  orderTitle: {
    color: '#f8fafc',
    fontSize: 16,
    fontWeight: '700',
  },
  orderMeta: {
    color: '#94a3b8',
    fontSize: 12,
  },
  orderTime: {
    color: '#fbbf24',
    fontSize: 12,
    fontWeight: '700',
  },
  orderTimeBlock: {
    alignItems: 'flex-end',
    gap: 2,
  },
  orderClock: {
    color: '#94a3b8',
    fontSize: 11,
  },
  itemCard: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#1e293b',
    padding: 12,
    gap: 6,
    backgroundColor: '#0b1528',
  },
  itemTitle: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 14,
  },
  itemNotes: {
    color: '#cbd5f5',
    fontSize: 12,
  },
  itemExtras: {
    color: '#94a3b8',
    fontSize: 12,
  },
  labelRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
    marginTop: 4,
  },
  statusBadge: {
    borderRadius: 999,
    borderWidth: 1,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  statusBadgeText: {
    color: '#e2e8f0',
    fontSize: 11,
    fontWeight: '600',
  },
  statusAction: {
    borderRadius: 999,
    backgroundColor: '#fbbf24',
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  statusActionDisabled: {
    opacity: 0.6,
  },
  statusActionText: {
    color: '#0f172a',
    fontWeight: '700',
    fontSize: 12,
  },
});

export default KitchenOrdersScreen;
