import React, {useEffect, useState} from 'react';
import {
  ActivityIndicator,
  Image,
  KeyboardAvoidingView,
  Modal,
  Platform,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {useAuth} from '../../context/AuthContext';
import {useServerSessions} from '../../context/ServerSessionsContext';
import {ServerStackParamList} from '../../navigation/serverTypes';
import {getAvailableServers, transferServerTableSession} from '../../services/api';
import {ServerUser} from '../../types';
import {formatTime, timeLeft} from '../../utils/serverOrderHelpers';

type Props = NativeStackScreenProps<ServerStackParamList, 'TableDetail'>;

const TableDetailScreen = ({navigation, route}: Props) => {
  const {sessionId} = route.params;
  const {user, token} = useAuth();
  const {
    getSessionById,
    refresh,
    refreshing,
    renewSession,
    closeSession,
    actionState,
  } = useServerSessions();
  const session = getSessionById(sessionId);
  const [tipModalVisible, setTipModalVisible] = useState(false);
  const [tipInput, setTipInput] = useState('');
  const [tipError, setTipError] = useState<string | null>(null);
  const [transferModalVisible, setTransferModalVisible] = useState(false);
  const [transferInput, setTransferInput] = useState('');
  const [transferError, setTransferError] = useState<string | null>(null);
  const [transferBusy, setTransferBusy] = useState(false);
  const [managerEmail, setManagerEmail] = useState('');
  const [managerPassword, setManagerPassword] = useState('');
  const [transferReason, setTransferReason] = useState('');
  const [availableServers, setAvailableServers] = useState<ServerUser[]>([]);
  const [loadingServers, setLoadingServers] = useState(false);
  const [selectedServerId, setSelectedServerId] = useState<number | null>(null);

  if (!session) {
    return (
      <View style={styles.container}>
        <Text style={styles.emptyText}>Mesa no encontrada.</Text>
      </View>
    );
  }

  const minutes = timeLeft(session.expires_at);
  const statusText =
    session.status === 'expired'
      ? 'QR expirado'
      : minutes !== null
      ? `Expira en ${minutes} min`
      : 'Expiración no disponible';
  const timeclock = session.timeclock;
  const orderModeLabel =
    (session.order_mode ?? 'table') === 'traditional'
      ? 'Tradicional'
      : 'Mesa ordena';
  const qrImageUrl = session.qr_url
    ? `https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=${encodeURIComponent(
        session.qr_url,
      )}`
    : null;
  const orders = [...(session.orders ?? [])].sort((a, b) => {
    const priority = (status: string) => {
      if (status === 'pending') return 0;
      if (status === 'confirmed') return 1;
      return 2;
    };
    const statusDiff = priority(a.status) - priority(b.status);
    if (statusDiff !== 0) {
      return statusDiff;
    }
    const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
    const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
    return dateB - dateA;
  });
  const isRenewing = actionState.renewingSessionId === session.id;
  const isClosing = actionState.closingSessionId === session.id;
  const isSessionBusy = isRenewing || isClosing;
  const canCharge = session.open_order_id != null && session.status !== 'closed';
  const hasSentOrders = orders.some(order => order.status !== 'cancelled');
  const isManager = user?.role === 'manager';
  const selectableServers = availableServers.filter(
    server => server.id !== session.server_id,
  );

  useEffect(() => {
    const loadServers = async () => {
      if (!transferModalVisible || !token) {
        return;
      }
      setLoadingServers(true);
      try {
        const servers = await getAvailableServers(token);
        setAvailableServers(servers);
      } catch (err) {
        setTransferError(
          err instanceof Error ? err.message : 'No se pudieron cargar.',
        );
      } finally {
        setLoadingServers(false);
      }
    };

    loadServers();
  }, [transferModalVisible, token]);

  const closeWithTip = async () => {
    const normalized = tipInput.trim().replace(',', '.');
    if (normalized.length === 0) {
      setTipError(null);
      setTipModalVisible(false);
      await closeSession(session.id);
      return;
    }

    const parsed = Number(normalized);
    if (Number.isNaN(parsed) || parsed < 0) {
      setTipError('Ingresa un monto valido.');
      return;
    }

    setTipError(null);
    setTipModalVisible(false);
    await closeSession(session.id, parsed);
  };

  const handleTransfer = async () => {
    const nextLabel = transferInput.trim();
    if (!nextLabel) {
      setTransferError('Ingresa el numero de mesa.');
      return;
    }
    if (!selectedServerId) {
      setTransferError('Selecciona el mesero destino.');
      return;
    }
    if (!isManager && (!managerEmail.trim() || !managerPassword.trim())) {
      setTransferError('Ingresa las credenciales del gerente.');
      return;
    }
    if (!token) {
      setTransferError('Sesion invalida.');
      return;
    }
    setTransferBusy(true);
    setTransferError(null);
    try {
      await transferServerTableSession(token, session.id, {
        table_label: nextLabel,
        server_id: selectedServerId,
        manager_email: !isManager ? managerEmail.trim() : undefined,
        manager_password: !isManager ? managerPassword : undefined,
        reason: transferReason.trim() || undefined,
      });
      setTransferModalVisible(false);
      setTransferInput('');
      refresh();
    } catch (err) {
      setTransferError(
        err instanceof Error ? err.message : 'No se pudo transferir.',
      );
    } finally {
      setTransferBusy(false);
    }
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
        <Text style={styles.heading}>Mesa {session.table_label}</Text>
        <Text style={styles.meta}>
          {session.guest_name} · {session.party_size} personas
        </Text>
        <Text style={styles.meta}>Modo: {orderModeLabel}</Text>
        <Text style={styles.meta}>{statusText}</Text>
        {timeclock ? (
          <View style={styles.timeclockCard}>
            <Text style={styles.timeclockTitle}>Tiempo en mesa</Text>
            <Text style={styles.timeclockText}>
              Transcurrido: {timeclock.elapsed_minutes ?? 0} min
            </Text>
            <Text style={styles.timeclockText}>
              Estimado: {timeclock.estimated_turn_minutes ?? 0} min · Restan{' '}
              {timeclock.remaining_minutes ?? 0} min
            </Text>
            <View style={styles.timeclockRow}>
              <Text style={styles.timeclockLabel}>Sentado</Text>
              <Text style={styles.timeclockValue}>
                {formatTime(session.seated_at) ?? '--'}
              </Text>
            </View>
            <View style={styles.timeclockRow}>
              <Text style={styles.timeclockLabel}>Primera orden</Text>
              <Text style={styles.timeclockValue}>
                {formatTime(session.first_order_at) ?? '--'}
              </Text>
            </View>
            <View style={styles.timeclockRow}>
              <Text style={styles.timeclockLabel}>Pago</Text>
              <Text style={styles.timeclockValue}>
                {formatTime(session.paid_at) ?? '--'}
              </Text>
            </View>
          </View>
        ) : null}

        {qrImageUrl ? (
          <Image source={{uri: qrImageUrl}} style={styles.qrImage} />
        ) : null}

        <View style={styles.actions}>
          <TouchableOpacity
            style={[styles.actionButton, styles.orderButton]}
            onPress={() => navigation.navigate('TakeOrder', {sessionId})}>
            <Text style={styles.orderText}>Tomar orden</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.actionButton, styles.payButton]}
            onPress={() => navigation.navigate('ServerPayment', {sessionId})}
            disabled={!canCharge || isSessionBusy}>
            <Text style={styles.payText}>Cobrar</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.actionButton, styles.primaryButton]}
            onPress={() => renewSession(session.id)}
            disabled={isSessionBusy || session.status === 'closed'}>
            {isRenewing ? (
              <ActivityIndicator color="#0f172a" />
            ) : (
              <Text style={styles.actionText}>Renovar 1h</Text>
            )}
          </TouchableOpacity>
          {hasSentOrders ? (
            <TouchableOpacity
              style={[styles.actionButton, styles.transferButton]}
              onPress={() => {
                setTransferInput('');
                setTransferError(null);
                setManagerEmail('');
                setManagerPassword('');
                setTransferReason('');
                setSelectedServerId(null);
                setTransferModalVisible(true);
              }}
              disabled={isSessionBusy || session.status === 'closed'}>
              {transferBusy ? (
                <ActivityIndicator color="#0f172a" />
              ) : (
                <Text style={styles.transferText}>Transferir mesa</Text>
              )}
            </TouchableOpacity>
          ) : (
            <TouchableOpacity
              style={[styles.actionButton, styles.cancelButton]}
              onPress={() => {
                setTipInput('');
                setTipError(null);
                setTipModalVisible(true);
              }}
              disabled={isSessionBusy || session.status === 'closed'}>
              {isClosing ? (
                <ActivityIndicator color="#f8fafc" />
              ) : (
                <Text style={styles.cancelText}>Cerrar cuenta</Text>
              )}
            </TouchableOpacity>
          )}
        </View>
      </View>

      <View style={styles.card}>
        <Text style={styles.heading}>Órdenes</Text>
        {orders.length ? (
          orders.map(order => (
            <TouchableOpacity
              key={order.id}
              style={styles.orderRow}
              onPress={() =>
                navigation.navigate('OrderDetail', {
                  orderId: order.id,
                  sessionId: session.id,
                })
              }>
              <View>
                <Text style={styles.orderTitle}>
                  {order.order_id
                    ? `Ticket #${order.order_id} · Envio #${order.id}`
                    : `Orden #${order.id}`}
                </Text>
                <Text style={styles.orderMeta}>
                  {order.status}
                  {order.clover_status
                    ? ` · Clover: ${String(order.clover_status).toUpperCase()}`
                    : ''}
                  {formatTime(order.created_at)
                    ? ` · ${formatTime(order.created_at)}`
                    : ''}
                </Text>
              </View>
              <Text style={styles.orderLink}>Ver</Text>
            </TouchableOpacity>
          ))
        ) : (
          <Text style={styles.emptyText}>Aún no hay órdenes.</Text>
        )}
      </View>

      <Modal
        transparent
        visible={tipModalVisible}
        animationType="fade"
        onRequestClose={() => setTipModalVisible(false)}>
        <KeyboardAvoidingView
          style={styles.modalOverlay}
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Cerrar cuenta</Text>
            <Text style={styles.modalSubtitle}>
              Agrega la propina si aplica.
            </Text>
            <TextInput
              style={styles.tipInput}
              placeholder="$0.00"
              placeholderTextColor="#94a3b8"
              value={tipInput}
              onChangeText={setTipInput}
              keyboardType="decimal-pad"
            />
            {tipError ? <Text style={styles.errorText}>{tipError}</Text> : null}
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={[styles.actionButton, styles.modalCancel]}
                onPress={() => setTipModalVisible(false)}
                disabled={isClosing}>
                <Text style={styles.modalCancelText}>Cancelar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.actionButton, styles.modalConfirm]}
                onPress={closeWithTip}
                disabled={isClosing}>
                {isClosing ? (
                  <ActivityIndicator color="#0f172a" />
                ) : (
                  <Text style={styles.modalConfirmText}>Cerrar</Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </KeyboardAvoidingView>
      </Modal>

      <Modal
        transparent
        visible={transferModalVisible}
        animationType="fade"
        onRequestClose={() => setTransferModalVisible(false)}>
        <KeyboardAvoidingView
          style={styles.modalOverlay}
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Transferir mesa</Text>
            <Text style={styles.modalSubtitle}>
              Ingresa el nuevo numero de mesa.
            </Text>
            <TextInput
              style={styles.tipInput}
              placeholder="Mesa 12"
              placeholderTextColor="#94a3b8"
              value={transferInput}
              onChangeText={setTransferInput}
              keyboardType="default"
            />
            <Text style={styles.modalSubtitle}>Mesero destino</Text>
            {loadingServers ? (
              <ActivityIndicator color="#fbbf24" />
            ) : selectableServers.length ? (
              <View style={styles.serverList}>
                {selectableServers.map(server => {
                  const isSelected = selectedServerId === server.id;
                  return (
                    <TouchableOpacity
                      key={`server-${server.id}`}
                      style={[
                        styles.serverChip,
                        isSelected && styles.serverChipActive,
                      ]}
                      onPress={() => setSelectedServerId(server.id)}>
                      <Text
                        style={[
                          styles.serverChipText,
                          isSelected && styles.serverChipTextActive,
                        ]}>
                        {server.name}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </View>
            ) : (
              <Text style={styles.emptyText}>No hay meseros disponibles.</Text>
            )}
            {!isManager ? (
              <>
                <TextInput
                  style={styles.tipInput}
                  placeholder="Correo del gerente"
                  placeholderTextColor="#94a3b8"
                  value={managerEmail}
                  onChangeText={setManagerEmail}
                  keyboardType="email-address"
                  autoCapitalize="none"
                />
                <TextInput
                  style={styles.tipInput}
                  placeholder="Password del gerente"
                  placeholderTextColor="#94a3b8"
                  value={managerPassword}
                  onChangeText={setManagerPassword}
                  secureTextEntry
                />
              </>
            ) : null}
            <TextInput
              style={styles.tipInput}
              placeholder="Motivo (opcional)"
              placeholderTextColor="#94a3b8"
              value={transferReason}
              onChangeText={setTransferReason}
            />
            {transferError ? (
              <Text style={styles.errorText}>{transferError}</Text>
            ) : null}
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={[styles.actionButton, styles.modalCancel]}
                onPress={() => setTransferModalVisible(false)}
                disabled={transferBusy}>
                <Text style={styles.modalCancelText}>Cancelar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.actionButton, styles.modalConfirm]}
                onPress={handleTransfer}
                disabled={transferBusy}>
                {transferBusy ? (
                  <ActivityIndicator color="#0f172a" />
                ) : (
                  <Text style={styles.modalConfirmText}>Transferir</Text>
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
  payButton: {
    backgroundColor: '#22c55e',
    borderColor: '#22c55e',
  },
  payText: {
    color: '#0f172a',
    fontWeight: '700',
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
    fontSize: 18,
    fontWeight: '700',
    color: '#f8fafc',
  },
  meta: {
    color: '#94a3b8',
    fontSize: 13,
  },
  timeclockCard: {
    backgroundColor: '#111827',
    borderRadius: 16,
    padding: 12,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 4,
  },
  timeclockTitle: {
    color: '#fbbf24',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 1,
  },
  timeclockText: {
    color: '#e2e8f0',
    fontSize: 12,
  },
  timeclockRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  timeclockLabel: {
    color: '#94a3b8',
    fontSize: 12,
  },
  timeclockValue: {
    color: '#e2e8f0',
    fontSize: 12,
    fontWeight: '600',
  },
  qrImage: {
    width: '100%',
    aspectRatio: 1,
    borderRadius: 16,
    backgroundColor: '#020617',
  },
  actions: {
    gap: 10,
  },
  actionButton: {
    borderRadius: 999,
    paddingVertical: 12,
    alignItems: 'center',
  },
  orderButton: {
    borderWidth: 1,
    borderColor: '#38bdf8',
  },
  orderText: {
    color: '#38bdf8',
    fontWeight: '700',
  },
  primaryButton: {
    backgroundColor: '#fbbf24',
  },
  cancelButton: {
    borderWidth: 1,
    borderColor: '#fb7185',
  },
  transferButton: {
    borderWidth: 1,
    borderColor: '#f59e0b',
    backgroundColor: '#f59e0b',
  },
  actionText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  cancelText: {
    color: '#fb7185',
    fontWeight: '600',
  },
  transferText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  orderRow: {
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#1f2937',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  orderTitle: {
    color: '#f8fafc',
    fontWeight: '700',
  },
  orderMeta: {
    color: '#94a3b8',
    fontSize: 12,
  },
  orderLink: {
    color: '#fbbf24',
    fontWeight: '700',
  },
  emptyText: {
    color: '#94a3b8',
    textAlign: 'center',
    marginTop: 10,
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
  tipInput: {
    backgroundColor: '#1e293b',
    borderRadius: 14,
    paddingHorizontal: 14,
    paddingVertical: 10,
    color: '#f8fafc',
  },
  errorText: {
    color: '#fb7185',
    fontSize: 12,
  },
  modalActions: {
    flexDirection: 'row',
    gap: 10,
  },
  serverList: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  serverChip: {
    borderWidth: 1,
    borderColor: '#334155',
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  serverChipActive: {
    backgroundColor: '#38bdf8',
    borderColor: '#38bdf8',
  },
  serverChipText: {
    color: '#e2e8f0',
    fontSize: 12,
    fontWeight: '600',
  },
  serverChipTextActive: {
    color: '#0f172a',
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

export default TableDetailScreen;
