import React, {useEffect, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Modal,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {useServerSessions} from '../../context/ServerSessionsContext';
import {useAuth} from '../../context/AuthContext';
import {ServerStackParamList} from '../../navigation/serverTypes';
import {
  overrideServerOrderItem,
  voidServerOrderItem,
} from '../../services/api';
import {
  formatDuration,
  formatTime,
  groupItemsByCategory,
  labelStatusLabel,
  scopeLabel,
} from '../../utils/serverOrderHelpers';

type Props = NativeStackScreenProps<ServerStackParamList, 'OrderDetail'>;

const OrderDetailScreen = ({route}: Props) => {
  const {orderId} = route.params;
  const {user, token} = useAuth();
  const {getOrderById, confirmOrder, cancelOrder, actionState, refresh} =
    useServerSessions();
  const result = getOrderById(orderId);
  const [voidingItemId, setVoidingItemId] = useState<number | null>(null);
  const [overrideVisible, setOverrideVisible] = useState(false);
  const [overrideItemId, setOverrideItemId] = useState<number | null>(null);
  const [managerEmail, setManagerEmail] = useState('');
  const [managerPassword, setManagerPassword] = useState('');
  const [overrideReason, setOverrideReason] = useState('');
  const [overrideError, setOverrideError] = useState<string | null>(null);
  const [overrideSubmitting, setOverrideSubmitting] = useState(false);
  const [, setTick] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setTick(prev => prev + 1);
    }, 30000);
    return () => clearInterval(interval);
  }, []);

  if (!result) {
    return (
      <View style={styles.container}>
        <Text style={styles.emptyText}>Orden no encontrada.</Text>
      </View>
    );
  }

  const {order, session} = result;
  const groups = groupItemsByCategory(order.items ?? []);
  const isManager = user?.role === 'manager';
  const canConfirm = order.status === 'pending';
  const canCancel =
    order.status === 'pending' || (isManager && order.status === 'confirmed');
  const isConfirming = actionState.confirmingOrderId === order.id;
  const isCancelling = actionState.cancellingOrderId === order.id;
  const isBusy = isConfirming || isCancelling;

  const canVoidPending = order.status === 'pending';
  const canVoidConfirmedManager = order.status === 'confirmed' && isManager;
  const needsOverride = order.status === 'confirmed' && !isManager;

  const handleVoidItem = async (itemId: number, label: string) => {
    if (!token) {
      return;
    }
    Alert.alert(
      'Eliminar item',
      `¿Eliminar "${label}" de esta orden?`,
      [
        {text: 'Cancelar', style: 'cancel'},
        {
          text: 'Eliminar',
          style: 'destructive',
          onPress: async () => {
            try {
              setVoidingItemId(itemId);
              await voidServerOrderItem(token, order.id, itemId);
              await refresh();
            } catch (err) {
              // El mensaje ya viene del backend.
            } finally {
              setVoidingItemId(null);
            }
          },
        },
      ],
    );
  };

  const openOverride = (itemId: number) => {
    setOverrideItemId(itemId);
    setManagerEmail('');
    setManagerPassword('');
    setOverrideReason('');
    setOverrideError(null);
    setOverrideVisible(true);
  };

  const handleOverride = async () => {
    if (!token || !overrideItemId) {
      return;
    }
    setOverrideSubmitting(true);
    setOverrideError(null);
    try {
      await overrideServerOrderItem(
        token,
        order.id,
        overrideItemId,
        managerEmail.trim(),
        managerPassword,
        overrideReason.trim() || undefined,
      );
      await refresh();
      setOverrideVisible(false);
    } catch (err) {
      setOverrideError(
        err instanceof Error ? err.message : 'No se pudo autorizar.',
      );
    } finally {
      setOverrideSubmitting(false);
    }
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      <View style={styles.card}>
        <Text style={styles.heading}>
          {order.order_id
            ? `Ticket #${order.order_id} · Envio #${order.id}`
            : `Orden #${order.id}`}
        </Text>
        <Text style={styles.meta}>
          Mesa {session.table_label} · {session.guest_name}
        </Text>
        <Text style={styles.meta}>
          {formatTime(order.created_at)
            ? `Recibida ${formatTime(order.created_at)}`
            : 'Hora no disponible'}
        </Text>
        <View style={styles.statusChip}>
          <Text style={styles.statusText}>{order.status}</Text>
        </View>
      </View>

      <View style={styles.card}>
        {groups.map(group => (
          <View key={`${group.scope}-${group.categoryName}`} style={styles.group}>
            <Text style={styles.groupTitle}>
              {scopeLabel(group.scope)} · {group.categoryName}
            </Text>
            {group.items.map(item => (
              <View key={item.id} style={styles.itemRow}>
                <Text style={styles.itemName}>
                  {item.quantity}x {item.name}
                </Text>
                {item.extras?.length ? (
                  <Text style={styles.itemExtras}>
                    {Object.entries(
                      item.extras.reduce<Record<string, string[]>>(
                        (groups, extra) => {
                          const group = extra.group_name || 'Opciones';
                          if (!groups[group]) {
                            groups[group] = [];
                          }
                          groups[group].push(extra.name);
                          return groups;
                        },
                        {},
                      ),
                    )
                      .map(
                        ([group, names]) =>
                          `${group}: ${names.join(', ')}`,
                      )
                      .join(' · ')}
                  </Text>
                ) : null}
                {item.notes ? (
                  <Text style={styles.itemNotes}>Nota: {item.notes}</Text>
                ) : null}
                {item.labels?.length ? (
                  <View style={styles.labelList}>
                    {item.labels.map(label => {
                      const startAt = label.prepared_at ?? order.created_at;
                      const duration = formatDuration(
                        startAt,
                        label.delivered_at ?? null,
                      );
                      return (
                        <View key={`${item.id}-label-${label.id}`} style={styles.labelRow}>
                          <View style={styles.labelInfo}>
                            <Text style={styles.labelName}>{label.name}</Text>
                            <Text style={styles.labelStatus}>
                              {labelStatusLabel(label.status)}
                            </Text>
                          </View>
                          {duration ? (
                            <Text style={styles.labelTime}>{duration}</Text>
                          ) : null}
                        </View>
                      );
                    })}
                  </View>
                ) : null}
                {canVoidPending || canVoidConfirmedManager ? (
                  <TouchableOpacity
                    style={styles.itemAction}
                    onPress={() => handleVoidItem(item.id, item.name)}
                    disabled={voidingItemId === item.id}>
                    {voidingItemId === item.id ? (
                      <ActivityIndicator color="#fbbf24" size="small" />
                    ) : (
                      <Text style={styles.itemActionText}>
                        {canVoidPending ? 'Eliminar' : 'Anular'}
                      </Text>
                    )}
                  </TouchableOpacity>
                ) : null}
                {needsOverride ? (
                  <TouchableOpacity
                    style={styles.itemAction}
                    onPress={() => openOverride(item.id)}>
                    <Text style={styles.itemActionText}>
                      Anular (gerente)
                    </Text>
                  </TouchableOpacity>
                ) : null}
              </View>
            ))}
          </View>
        ))}
      </View>

      {canConfirm || canCancel ? (
        <View style={styles.actions}>
          {canConfirm ? (
            <TouchableOpacity
              style={[styles.actionButton, styles.confirmButton]}
              onPress={() => confirmOrder(order.id)}
              disabled={isBusy}>
              {isConfirming ? (
                <ActivityIndicator color="#0f172a" />
              ) : (
                <Text style={styles.confirmText}>Confirmar orden</Text>
              )}
            </TouchableOpacity>
          ) : null}
          {canCancel ? (
            <TouchableOpacity
              style={[styles.actionButton, styles.cancelButton]}
              onPress={() => cancelOrder(order.id)}
              disabled={isBusy}>
              {isCancelling ? (
                <ActivityIndicator color="#f8fafc" />
              ) : (
                <Text style={styles.cancelText}>
                  {order.status === 'confirmed' ? 'Anular orden' : 'Cancelar'}
                </Text>
              )}
            </TouchableOpacity>
          ) : null}
        </View>
      ) : null}

      <Modal
        transparent
        visible={overrideVisible}
        animationType="fade"
        onRequestClose={() => setOverrideVisible(false)}>
        <KeyboardAvoidingView
          style={styles.modalOverlay}
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Autorización gerente</Text>
            <Text style={styles.modalSubtitle}>
              Ingresa las credenciales del gerente para anular el item.
            </Text>
            <TextInput
              style={styles.modalInput}
              placeholder="Correo del gerente"
              placeholderTextColor="#94a3b8"
              value={managerEmail}
              onChangeText={setManagerEmail}
              autoCapitalize="none"
              keyboardType="email-address"
            />
            <TextInput
              style={styles.modalInput}
              placeholder="Contraseña"
              placeholderTextColor="#94a3b8"
              value={managerPassword}
              onChangeText={setManagerPassword}
              secureTextEntry
            />
            <TextInput
              style={styles.modalInput}
              placeholder="Motivo (opcional)"
              placeholderTextColor="#94a3b8"
              value={overrideReason}
              onChangeText={setOverrideReason}
            />
            {overrideError ? (
              <Text style={styles.modalError}>{overrideError}</Text>
            ) : null}
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={[styles.actionButton, styles.modalCancel]}
                onPress={() => setOverrideVisible(false)}
                disabled={overrideSubmitting}>
                <Text style={styles.modalCancelText}>Cancelar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.actionButton, styles.modalConfirm]}
                onPress={handleOverride}
                disabled={overrideSubmitting}>
                {overrideSubmitting ? (
                  <ActivityIndicator color="#0f172a" />
                ) : (
                  <Text style={styles.modalConfirmText}>Autorizar</Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </KeyboardAvoidingView>
      </Modal>
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
    gap: 8,
  },
  heading: {
    fontSize: 18,
    fontWeight: '700',
    color: '#f8fafc',
  },
  meta: {
    color: '#94a3b8',
    fontSize: 13,
  },
  statusChip: {
    alignSelf: 'flex-start',
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  statusText: {
    color: '#0f172a',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  group: {
    gap: 6,
  },
  groupTitle: {
    color: '#fbbf24',
    fontWeight: '700',
  },
  itemRow: {
    gap: 2,
  },
  itemName: {
    color: '#f8fafc',
    fontSize: 14,
  },
  itemExtras: {
    color: '#94a3b8',
    fontSize: 12,
  },
  itemNotes: {
    color: '#cbd5f5',
    fontSize: 12,
  },
  labelList: {
    marginTop: 6,
    gap: 4,
  },
  labelRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 6,
    paddingHorizontal: 10,
    borderRadius: 12,
    backgroundColor: '#0b1220',
    borderWidth: 1,
    borderColor: '#1f2937',
  },
  labelInfo: {
    flex: 1,
    gap: 2,
  },
  labelName: {
    color: '#f8fafc',
    fontSize: 12,
    fontWeight: '700',
  },
  labelStatus: {
    color: '#94a3b8',
    fontSize: 11,
  },
  labelTime: {
    color: '#fbbf24',
    fontSize: 11,
    fontWeight: '700',
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
    fontSize: 12,
    fontWeight: '700',
  },
  actions: {
    gap: 12,
  },
  actionButton: {
    borderRadius: 999,
    paddingVertical: 14,
    alignItems: 'center',
  },
  confirmButton: {
    backgroundColor: '#22c55e',
  },
  confirmText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  cancelButton: {
    borderWidth: 1,
    borderColor: '#fb7185',
  },
  cancelText: {
    color: '#fb7185',
    fontWeight: '700',
  },
  emptyText: {
    color: '#94a3b8',
    textAlign: 'center',
    marginTop: 20,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(2, 6, 23, 0.85)',
    justifyContent: 'center',
    padding: 20,
  },
  modalCard: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 20,
    borderWidth: 1,
    borderColor: '#1e293b',
    gap: 10,
  },
  modalTitle: {
    color: '#f8fafc',
    fontSize: 16,
    fontWeight: '700',
  },
  modalSubtitle: {
    color: '#94a3b8',
    fontSize: 12,
  },
  modalInput: {
    backgroundColor: '#1e293b',
    borderRadius: 14,
    paddingHorizontal: 14,
    paddingVertical: 10,
    color: '#f8fafc',
  },
  modalError: {
    color: '#fb7185',
    fontSize: 12,
  },
  modalActions: {
    flexDirection: 'row',
    gap: 10,
  },
  modalCancel: {
    flex: 1,
    borderWidth: 1,
    borderColor: '#334155',
  },
  modalCancelText: {
    color: '#e2e8f0',
    fontWeight: '600',
  },
  modalConfirm: {
    flex: 1,
    backgroundColor: '#fbbf24',
  },
  modalConfirmText: {
    color: '#0f172a',
    fontWeight: '700',
  },
});

export default OrderDetailScreen;
