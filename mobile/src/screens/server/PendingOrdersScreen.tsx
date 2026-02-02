import React, {useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {useNavigation} from '@react-navigation/native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {useAuth} from '../../context/AuthContext';
import {useServerSessions} from '../../context/ServerSessionsContext';
import {ServerStackParamList} from '../../navigation/serverTypes';
import {updateKitchenOrderItemStatus} from '../../services/api';
import {
  formatDuration,
  formatTime,
  getKitchenSummary,
  labelStatusLabel,
} from '../../utils/serverOrderHelpers';

const PendingOrdersScreen = () => {
  const {user, token} = useAuth();
  const navigation =
    useNavigation<NativeStackNavigationProp<ServerStackParamList>>();
  const {
    pendingOrders,
    pendingOrdersTotal,
    loading,
    refreshing,
    error,
    newOrderNotice,
    kitchenNotice,
    refresh,
  } = useServerSessions();
  const [tick, setTick] = useState(0);
  const [delivering, setDelivering] = useState<Record<number, boolean>>({});
  const [localError, setLocalError] = useState<string | null>(null);

  const orderById = useMemo(() => {
    return pendingOrders.reduce<Record<number, typeof pendingOrders[number]>>(
      (acc, entry) => {
        acc[entry.order.id] = entry;
        return acc;
      },
      {},
    );
  }, [pendingOrders]);

  useEffect(() => {
    const interval = setInterval(() => {
      setTick(prev => prev + 1);
    }, 30000);
    return () => clearInterval(interval);
  }, []);

  const deliverOrder = async (orderId: number) => {
    if (!token) {
      return;
    }
    const entry = orderById[orderId];
    if (!entry) {
      return;
    }
    const items = entry.order.items ?? [];
    const readyLabels = items.flatMap(item =>
      (item.labels ?? [])
        .filter(label => label.status === 'ready')
        .map(label => ({itemId: item.id, labelId: label.id})),
    );

    if (!readyLabels.length) {
      Alert.alert('Sin items listos', 'Aún no hay items listos para entregar.');
      return;
    }

    setDelivering(prev => ({...prev, [orderId]: true}));
    setLocalError(null);
    try {
      await Promise.all(
        readyLabels.map(entryLabel =>
          updateKitchenOrderItemStatus(
            token,
            entryLabel.itemId,
            entryLabel.labelId,
            'delivered',
          ),
        ),
      );
      refresh();
    } catch (err) {
      setLocalError(
        err instanceof Error ? err.message : 'No se pudo entregar.',
      );
    } finally {
      setDelivering(prev => {
        const next = {...prev};
        delete next[orderId];
        return next;
      });
    }
  };

  return (
    <View style={styles.container}>
      {loading && pendingOrdersTotal === 0 ? (
        <View style={styles.loader}>
          <ActivityIndicator color="#fbbf24" />
        </View>
      ) : (
        <FlatList
          data={pendingOrders}
          keyExtractor={item => `pending-${item.order.id}`}
          extraData={tick}
          refreshControl={
            <RefreshControl
              tintColor="#fbbf24"
              refreshing={refreshing}
              onRefresh={refresh}
            />
          }
          ListHeaderComponent={
            <View style={styles.header}>
              <Text style={styles.greeting}>Hola {user?.name}</Text>
              <Text style={styles.subheading}>
                Órdenes pendientes ({pendingOrdersTotal})
              </Text>
              {newOrderNotice ? (
                <View style={styles.noticeBanner}>
                  <Text style={styles.noticeText}>
                    {newOrderNotice === 1
                      ? 'Nueva orden recibida'
                      : `Nuevas órdenes (${newOrderNotice})`}
                  </Text>
                </View>
              ) : null}
              {kitchenNotice ? (
                <View style={styles.readyBanner}>
                  <Text style={styles.readyText}>
                    Orden #{kitchenNotice.orderId} lista en cocina
                  </Text>
                </View>
              ) : null}
              {localError ? (
                <Text style={styles.error}>{localError}</Text>
              ) : null}
              {error ? <Text style={styles.error}>{error}</Text> : null}
            </View>
          }
          ListEmptyComponent={
            <Text style={styles.emptyText}>No hay órdenes pendientes.</Text>
          }
          renderItem={({item}) => {
            const sentLabel = formatTime(item.order.created_at);
            const kitchenSummary = getKitchenSummary(item.order);
            const kitchenDuration = formatDuration(
              kitchenSummary?.startAt ?? item.order.created_at ?? null,
              kitchenSummary?.endAt ?? null,
            );
            const canDeliver = kitchenSummary?.status === 'ready';
            return (
              <TouchableOpacity
                style={styles.card}
                onPress={() =>
                  navigation.navigate('OrderDetail', {
                    orderId: item.order.id,
                    sessionId: item.session.id,
                  })
                }>
                <View style={styles.cardRow}>
                  <Text style={styles.cardTitle}>
                    Mesa {item.session.table_label}
                  </Text>
                  <View style={styles.pendingBadge}>
                    <Text style={styles.pendingBadgeText}>Pendiente</Text>
                  </View>
                </View>
                <Text style={styles.cardMeta}>
                  {item.session.guest_name} · {item.session.party_size} personas
                </Text>
                <Text style={styles.cardMeta}>
                  {item.order.order_id
                    ? `Ticket #${item.order.order_id} · Envio #${item.order.id}`
                    : `Orden #${item.order.id}`}
                </Text>
                <Text style={styles.cardMeta}>
                  {sentLabel ? `Enviada ${sentLabel}` : 'Hora no disponible'}
                </Text>
                {kitchenSummary ? (
                  <Text style={styles.cardMeta}>
                    Cocina: {labelStatusLabel(kitchenSummary.status)}
                  </Text>
                ) : (
                  <Text style={styles.cardMeta}>Cocina: Pendiente</Text>
                )}
                {kitchenDuration ? (
                  <Text style={styles.cardMeta}>
                    Tiempo en cocina: {kitchenDuration}
                  </Text>
                ) : null}
                {canDeliver ? (
                  <TouchableOpacity
                    style={styles.deliverButton}
                    onPress={() => deliverOrder(item.order.id)}
                    disabled={delivering[item.order.id]}>
                    {delivering[item.order.id] ? (
                      <ActivityIndicator color="#0f172a" />
                    ) : (
                      <Text style={styles.deliverText}>Marcar entregada</Text>
                    )}
                  </TouchableOpacity>
                ) : null}
              </TouchableOpacity>
            );
          }}
          contentContainerStyle={styles.listContent}
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#020617',
  },
  listContent: {
    padding: 20,
    gap: 12,
    paddingBottom: 40,
  },
  header: {
    gap: 8,
  },
  greeting: {
    fontSize: 16,
    fontWeight: '600',
    color: '#f8fafc',
  },
  subheading: {
    fontSize: 14,
    color: '#94a3b8',
  },
  loader: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  error: {
    color: '#fb7185',
    fontSize: 14,
  },
  noticeBanner: {
    backgroundColor: '#fbbf24',
    borderRadius: 16,
    paddingVertical: 10,
    paddingHorizontal: 14,
  },
  noticeText: {
    color: '#0f172a',
    fontWeight: '700',
    fontSize: 13,
  },
  readyBanner: {
    backgroundColor: '#22c55e',
    borderRadius: 16,
    paddingVertical: 10,
    paddingHorizontal: 14,
  },
  readyText: {
    color: '#0f172a',
    fontWeight: '700',
    fontSize: 13,
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 6,
  },
  cardRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  cardTitle: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 16,
  },
  cardMeta: {
    color: '#94a3b8',
    fontSize: 13,
  },
  deliverButton: {
    alignSelf: 'flex-start',
    marginTop: 10,
    backgroundColor: '#22c55e',
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 999,
  },
  deliverText: {
    color: '#0f172a',
    fontSize: 12,
    fontWeight: '700',
  },
  pendingBadge: {
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  pendingBadgeText: {
    color: '#0f172a',
    fontSize: 11,
    fontWeight: '700',
  },
  emptyText: {
    color: '#94a3b8',
    textAlign: 'center',
    marginTop: 16,
  },
});

export default PendingOrdersScreen;
