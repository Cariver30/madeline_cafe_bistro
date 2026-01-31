import React, {useEffect, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
  useWindowDimensions,
} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import {useNavigation} from '@react-navigation/native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {useAuth} from '../../context/AuthContext';
import {usePosTickets} from '../../context/PosTicketsContext';
import {PosStackParamList} from '../../navigation/posTypes';
import {
  formatDuration,
  formatTime,
  getKitchenSummary,
  labelStatusLabel,
} from '../../utils/serverOrderHelpers';

const PosHomeScreen = () => {
  const navigation =
    useNavigation<NativeStackNavigationProp<PosStackParamList>>();
  const {logout} = useAuth();
  const {
    tickets,
    loading,
    refreshing,
    refresh,
    error,
    pendingBatches,
    pendingTotal,
  } = usePosTickets();

  const {width} = useWindowDimensions();
  const isTablet = width >= 768;
  const numColumns = isTablet ? 2 : 1;
  const [tick, setTick] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setTick(prev => prev + 1);
    }, 30000);
    return () => clearInterval(interval);
  }, []);

  const renderPending = ({item}: any) => {
    const kitchenSummary = getKitchenSummary(item.order);
    const kitchenDuration = kitchenSummary
      ? formatDuration(kitchenSummary.startAt, kitchenSummary.endAt)
      : null;
    return (
    <TouchableOpacity
      style={[styles.card, styles.pendingCard]}
      onPress={() =>
        navigation.navigate('PosTicket', {ticketId: item.ticket.id})
      }>
      <View style={styles.rowBetween}>
        <Text style={styles.cardTitle}>
          Ticket #{item.ticket.ticket_id}
        </Text>
        <View style={styles.badge}>
          <Text style={styles.badgeText}>PENDIENTE</Text>
        </View>
      </View>
      <Text style={styles.cardMeta}>
        Envío #{item.order.id} · {item.ticket.guest_name}
      </Text>
      <Text style={styles.cardMeta}>
        {formatTime(item.order.created_at) ?? 'Hora no disponible'}
      </Text>
      {kitchenSummary ? (
        <Text style={styles.cardMeta}>
          Cocina: {labelStatusLabel(kitchenSummary.status)}
          {kitchenDuration ? ` · ${kitchenDuration}` : ''}
        </Text>
      ) : null}
    </TouchableOpacity>
    );
  };

  const renderTicket = ({item}: any) => (
    <TouchableOpacity
      style={styles.card}
      onPress={() =>
        navigation.navigate('PosTicket', {ticketId: item.id})
      }>
      <View style={styles.rowBetween}>
        <Text style={styles.cardTitle}>
          Ticket #{item.ticket_id ?? '—'}
        </Text>
        <Text style={styles.channelText}>
          {item.service_channel === 'phone' ? 'Teléfono' : 'Walk-in'}
        </Text>
      </View>
      <Text style={styles.cardMeta}>{item.guest_name}</Text>
      <Text style={styles.cardMeta}>
        {formatTime(item.created_at) ?? 'Hora no disponible'}
      </Text>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      {/* HEADER FIJO */}
      <View style={styles.header}>
        <View>
          <Text style={styles.title}>POS</Text>
          <Text style={styles.subtitle}>Walk-in y teléfono</Text>
        </View>

        <View style={styles.headerActions}>
          <TouchableOpacity
            style={styles.newButton}
            onPress={() => navigation.navigate('PosNewTicket')}>
            <Text style={styles.newButtonText}>Nuevo ticket</Text>
          </TouchableOpacity>
          <TouchableOpacity onPress={logout}>
            <Text style={styles.logoutText}>Cerrar sesión</Text>
          </TouchableOpacity>
        </View>
      </View>

      {loading ? (
        <View style={styles.loader}>
          <ActivityIndicator color="#fbbf24" />
        </View>
      ) : (
        <FlatList
          data={pendingBatches}
          key={`pending-${numColumns}`}
          numColumns={numColumns}
          keyExtractor={item => `pending-${item.order.id}`}
          renderItem={renderPending}
          extraData={tick}
          refreshControl={
            <RefreshControl
              tintColor="#fbbf24"
              refreshing={refreshing}
              onRefresh={refresh}
            />
          }
          ListHeaderComponent={
            <>
              <Text style={styles.sectionTitle}>
                Pendientes ({pendingTotal})
              </Text>

              {!pendingBatches.length && (
                <Text style={styles.emptyText}>
                  No hay envíos pendientes.
                </Text>
              )}

              <Text style={styles.sectionTitle}>Tickets abiertos</Text>
            </>
          }
          ListFooterComponent={
            <FlatList
              data={tickets}
              key={`tickets-${numColumns}`}
              numColumns={numColumns}
              keyExtractor={item => `ticket-${item.id}`}
              renderItem={renderTicket}
              scrollEnabled={false}
              ListEmptyComponent={
                <Text style={styles.emptyText}>
                  No hay tickets abiertos.
                </Text>
              }
            />
          }
          contentContainerStyle={styles.listContent}
        />
      )}

      {error ? <Text style={styles.error}>{error}</Text> : null}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#020617',
  },
  scroll: {
    flex: 1,
  },
  content: {
    padding: 20,
    gap: 16,
    paddingBottom: 40,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 12,
  },
  headerActions: {
    alignItems: 'flex-end',
    gap: 8,
  },
  title: {
    fontSize: 20,
    fontWeight: '700',
    color: '#f8fafc',
  },
  subtitle: {
    fontSize: 12,
    color: '#94a3b8',
  },
  newButton: {
    backgroundColor: '#fbbf24',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 999,
  },
  newButtonText: {
    color: '#0f172a',
    fontWeight: '700',
    fontSize: 12,
  },
  logoutButton: {
    paddingHorizontal: 8,
    paddingVertical: 6,
  },
  logoutText: {
    color: '#e2e8f0',
    fontSize: 12,
    fontWeight: '600',
  },
  loader: {
    paddingVertical: 24,
    alignItems: 'center',
  },
  sectionHeader: {
    marginTop: 8,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: '#f8fafc',
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 6,
  },
  rowBetween: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  cardTitle: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 15,
  },
  cardMeta: {
    color: '#94a3b8',
    fontSize: 12,
  },
  badge: {
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  badgeText: {
    color: '#0f172a',
    fontSize: 10,
    fontWeight: '700',
  },
  channelText: {
    color: '#38bdf8',
    fontWeight: '600',
    fontSize: 12,
  },
  emptyText: {
    color: '#94a3b8',
    fontSize: 12,
  },
  error: {
    color: '#fb7185',
    fontSize: 13,
  },
  listContent: {
  padding: 16,
  paddingBottom: 40,
},

pendingCard: {
  borderColor: '#fbbf24',
  borderWidth: 2,
},

});

export default PosHomeScreen;
