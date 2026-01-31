import React, {useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {PosStackParamList} from '../../navigation/posTypes';
import {usePosTickets} from '../../context/PosTicketsContext';
import {useAuth} from '../../context/AuthContext';
import {voidPosOrderItem} from '../../services/api';
import {formatTime} from '../../utils/serverOrderHelpers';

const PosTicketScreen = ({navigation, route}: NativeStackScreenProps<PosStackParamList, 'PosTicket'>) => {
  const {ticketId} = route.params;
  const {user, token} = useAuth();
  const {
    getTicketById,
    refresh,
    refreshing,
    confirmBatch,
    cancelBatch,
    payTicket,
    actionState,
  } = usePosTickets();
  const [voidingItemId, setVoidingItemId] = useState<number | null>(null);
  const ticket = getTicketById(ticketId);

  if (!ticket) {
    return (
      <View style={styles.container}>
        <Text style={styles.emptyText}>Ticket no encontrado.</Text>
      </View>
    );
  }

  const isManager = user?.role === 'manager';

  const handleVoidItem = async (batchId: number, itemId: number, label: string) => {
    if (!token) {
      return;
    }
    Alert.alert('Anular item', `¿Anular "${label}" del envío?`, [
      {text: 'Cancelar', style: 'cancel'},
      {
        text: 'Anular',
        style: 'destructive',
        onPress: async () => {
          try {
            setVoidingItemId(itemId);
            await voidPosOrderItem(token, batchId, itemId);
            await refresh();
          } catch (err) {
            // mensaje proviene del backend
          } finally {
            setVoidingItemId(null);
          }
        },
      },
    ]);
  };

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.content}
      refreshControl={
        <RefreshControl
          tintColor="#fbbf24"
          refreshing={refreshing}
          onRefresh={refresh}
        />
      }>
      <View style={styles.card}>
        <Text style={styles.heading}>Ticket #{ticket.ticket_id ?? '—'}</Text>
        <Text style={styles.meta}>Cliente: {ticket.guest_name}</Text>
        <Text style={styles.meta}>
          Canal: {ticket.service_channel === 'phone' ? 'Telefono' : 'Walk-in'}
        </Text>
        <View style={styles.actions}>
          <TouchableOpacity
            style={[styles.actionButton, styles.primaryButton]}
            onPress={() => navigation.navigate('PosTakeOrder', {ticketId})}>
            <Text style={styles.actionText}>Agregar items</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.actionButton, styles.payButton]}
            onPress={() => navigation.navigate('PosPayment', {ticketId})}
            disabled={actionState.payingTicketId === ticketId}>
            {actionState.payingTicketId === ticketId ? (
              <ActivityIndicator color="#0f172a" />
            ) : (
              <Text style={styles.payText}>Cobrar</Text>
            )}
          </TouchableOpacity>
        </View>
      </View>

      <View style={styles.card}>
        <Text style={styles.heading}>Envios</Text>
        {ticket.orders?.length ? (
          ticket.orders.map(order => {
            const allowVoid = isManager && order.status !== 'cancelled';
            return (
            <View key={order.id} style={styles.batchCard}>
              <View style={styles.rowBetween}>
                <Text style={styles.batchTitle}>Envio #{order.id}</Text>
                <Text style={styles.batchStatus}>{order.status}</Text>
              </View>
              <Text style={styles.batchMeta}>
                {formatTime(order.created_at) || 'Hora no disponible'}
              </Text>
              {order.items.map(item => (
                <View key={item.id} style={styles.itemRow}>
                  <Text style={styles.itemText}>
                    {item.quantity}x {item.name}
                  </Text>
                  {item.extras?.length ? (
                    <Text style={styles.itemExtras}>
                      {item.extras.map(extra => extra.name).join(', ')}
                    </Text>
                  ) : null}
                  {allowVoid ? (
                    <TouchableOpacity
                      style={styles.itemAction}
                      onPress={() => handleVoidItem(order.id, item.id, item.name)}
                      disabled={voidingItemId === item.id}>
                      {voidingItemId === item.id ? (
                        <ActivityIndicator color="#fbbf24" size="small" />
                      ) : (
                        <Text style={styles.itemActionText}>Anular item</Text>
                      )}
                    </TouchableOpacity>
                  ) : null}
                </View>
              ))}
              {order.status === 'pending' ? (
                <View style={styles.batchActions}>
                  <TouchableOpacity
                    style={[styles.actionButton, styles.confirmButton]}
                    onPress={() => confirmBatch(order.id)}
                    disabled={actionState.activeBatchId === order.id}>
                    {actionState.activeBatchId === order.id ? (
                      <ActivityIndicator color="#0f172a" />
                    ) : (
                      <Text style={styles.confirmText}>Confirmar</Text>
                    )}
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={[styles.actionButton, styles.cancelButton]}
                    onPress={() => cancelBatch(order.id)}
                    disabled={actionState.activeBatchId === order.id}>
                    {actionState.activeBatchId === order.id ? (
                      <ActivityIndicator color="#f8fafc" />
                    ) : (
                      <Text style={styles.cancelText}>Cancelar</Text>
                    )}
                  </TouchableOpacity>
                </View>
              ) : null}
            </View>
          )})
        ) : (
          <Text style={styles.emptyText}>Aun no hay envios.</Text>
        )}
      </View>

      <TouchableOpacity
        style={styles.secondaryButton}
        onPress={() => navigation.goBack()}>
        <Text style={styles.secondaryText}>Volver</Text>
      </TouchableOpacity>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#020617',
  },
  content: {
    padding: 20,
    gap: 16,
    paddingBottom: 40,
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 24,
    padding: 20,
    borderWidth: 1,
    borderColor: '#1e293b',
    gap: 10,
  },
  heading: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 16,
  },
  meta: {
    color: '#94a3b8',
    fontSize: 12,
  },
  actions: {
    gap: 10,
  },
  actionButton: {
    borderRadius: 999,
    paddingVertical: 12,
    alignItems: 'center',
  },
  primaryButton: {
    backgroundColor: '#38bdf8',
  },
  actionText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  payButton: {
    backgroundColor: '#22c55e',
  },
  payText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  batchCard: {
    borderWidth: 1,
    borderColor: '#1f2937',
    borderRadius: 16,
    padding: 12,
    gap: 6,
  },
  rowBetween: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  batchTitle: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 14,
  },
  batchStatus: {
    color: '#fbbf24',
    fontWeight: '600',
    fontSize: 12,
  },
  batchMeta: {
    color: '#94a3b8',
    fontSize: 11,
  },
  itemRow: {
    marginTop: 4,
  },
  itemText: {
    color: '#e2e8f0',
    fontSize: 12,
  },
  itemExtras: {
    color: '#94a3b8',
    fontSize: 10,
  },
  itemAction: {
    alignSelf: 'flex-start',
    paddingVertical: 6,
    paddingHorizontal: 10,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    marginTop: 6,
  },
  itemActionText: {
    color: '#fbbf24',
    fontSize: 11,
    fontWeight: '700',
  },
  batchActions: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 8,
  },
  confirmButton: {
    backgroundColor: '#fbbf24',
    flex: 1,
  },
  confirmText: {
    color: '#0f172a',
    fontWeight: '700',
    fontSize: 12,
  },
  cancelButton: {
    borderWidth: 1,
    borderColor: '#fb7185',
    flex: 1,
  },
  cancelText: {
    color: '#fb7185',
    fontWeight: '700',
    fontSize: 12,
  },
  emptyText: {
    color: '#94a3b8',
    fontSize: 12,
  },
  secondaryButton: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    paddingVertical: 12,
    alignItems: 'center',
  },
  secondaryText: {
    color: '#cbd5f5',
    fontWeight: '600',
  },
});

export default PosTicketScreen;
