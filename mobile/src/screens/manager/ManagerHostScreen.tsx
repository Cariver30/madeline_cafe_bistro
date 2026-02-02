import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Modal,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Switch,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';
import {SafeAreaView, useSafeAreaInsets} from 'react-native-safe-area-context';
import {useAuth} from '../../context/AuthContext';
import {
  assignWaitingListTables,
  createDiningTable,
  createWaitingListEntry,
  getAvailableServers,
  getDiningTables,
  getWaitingList,
  getWaitingListSettings,
  notifyWaitingListEntry,
  updateDiningTableStatus,
  updateWaitingListEntry,
  updateWaitingListSettings,
} from '../../services/api';
import {getEcho} from '../../services/realtime';
import {DiningTable, ServerUser, WaitingListEntry, WaitingListSettings} from '../../types';

const STATUS_COLORS: Record<string, string> = {
  waiting: '#fbbf24',
  notified: '#38bdf8',
  seated: '#22c55e',
  cancelled: '#94a3b8',
  no_show: '#f97316',
};

const TABLE_STATUS_COLORS: Record<string, string> = {
  available: '#22c55e',
  reserved: '#fbbf24',
  occupied: '#ef4444',
  occupied_unassigned: '#a855f7',
  dirty: '#f97316',
  out_of_service: '#64748b',
};

const formatMinutes = (value?: number | null) => {
  if (value === null || value === undefined || Number.isNaN(value)) {
    return '--';
  }
  return Math.max(0, Math.round(value));
};

const ManagerHostScreen = () => {
  const {token, user, logout} = useAuth();
  const insets = useSafeAreaInsets();
  const scope = user?.role === 'host' ? 'host' : 'manager';
  const isManager = user?.role === 'manager';
  const [view, setView] = useState<'waiting' | 'tables'>('waiting');
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [entries, setEntries] = useState<WaitingListEntry[]>([]);
  const [tables, setTables] = useState<DiningTable[]>([]);
  const [settings, setSettings] = useState<WaitingListSettings | null>(null);
  const [servers, setServers] = useState<ServerUser[]>([]);

  const [showEntryModal, setShowEntryModal] = useState(false);
  const [showAssignModal, setShowAssignModal] = useState(false);
  const [showSettingsModal, setShowSettingsModal] = useState(false);
  const [showTableModal, setShowTableModal] = useState(false);

  const [newEntry, setNewEntry] = useState({
    guest_name: '',
    guest_phone: '',
    guest_email: '',
    party_size: '2',
    quoted_minutes: '',
    notes: '',
  });

  const [newTable, setNewTable] = useState({
    label: '',
    capacity: '2',
    section: '',
  });

  const [assignTarget, setAssignTarget] = useState<WaitingListEntry | null>(null);
  const [assignMode, setAssignMode] = useState<'reserve' | 'seat'>('reserve');
  const [selectedTableIds, setSelectedTableIds] = useState<number[]>([]);
  const [selectedServerId, setSelectedServerId] = useState<number | null>(null);
  const [assignError, setAssignError] = useState<string | null>(null);
  const [assigning, setAssigning] = useState(false);

  const loadData = useCallback(
    async (showLoader = true) => {
      if (!token) {
        return;
      }
      try {
        if (showLoader) {
          setLoading(true);
        }
        const [tablesResult, entriesResult, settingsResult, serversResult] =
          await Promise.allSettled([
            getDiningTables(token, undefined, scope),
            getWaitingList(token, undefined, scope),
            getWaitingListSettings(token, scope),
            getAvailableServers(token, scope),
          ]);

        if (tablesResult.status === 'fulfilled') {
          setTables(tablesResult.value);
        }
        if (entriesResult.status === 'fulfilled') {
          setEntries(entriesResult.value);
        }
        if (settingsResult.status === 'fulfilled') {
          setSettings(settingsResult.value);
        }
        if (serversResult.status === 'fulfilled') {
          setServers(serversResult.value);
        }

        if (
          tablesResult.status === 'rejected' &&
          entriesResult.status === 'rejected'
        ) {
          throw tablesResult.reason;
        }

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
    [token, scope],
  );

  useFocusEffect(
    useCallback(() => {
      loadData();
    }, [loadData]),
  );

  useEffect(() => {
    if (!token) {
      return;
    }

    const echo = getEcho(token);
    const channel = echo.private('host.waiting-list');
    const handler = () => loadData(false);

    channel.listen('.HostDashboardUpdated', handler);

    return () => {
      channel.stopListening('.HostDashboardUpdated');
    };
  }, [token, loadData]);

  const availableTables = useMemo(
    () => tables.filter(table => table.status === 'available'),
    [tables],
  );

  const resolveTableBadge = (table: DiningTable) => {
    const unassignedOccupied =
      table.status === 'occupied' && !table.active_session;
    const statusKey = unassignedOccupied ? 'occupied_unassigned' : table.status;
    const label = unassignedOccupied ? 'ocupada (sin mesero)' : table.status;
    const color = TABLE_STATUS_COLORS[statusKey] || '#334155';
    return {label, color};
  };

  const visibleEntries = useMemo(
    () => entries.filter(entry => !['cancelled', 'no_show'].includes(entry.status)),
    [entries],
  );

  const handleCreateEntry = async () => {
    if (!token) return;
    try {
      const payload = {
        guest_name: newEntry.guest_name.trim(),
        guest_phone: newEntry.guest_phone.trim(),
        guest_email: newEntry.guest_email.trim() || null,
        party_size: Number(newEntry.party_size) || 1,
        quoted_minutes: newEntry.quoted_minutes
          ? Number(newEntry.quoted_minutes)
          : null,
        notes: newEntry.notes.trim() || null,
      };
      await createWaitingListEntry(token, payload, scope);
      setShowEntryModal(false);
      setNewEntry({
        guest_name: '',
        guest_phone: '',
        guest_email: '',
        party_size: '2',
        quoted_minutes: '',
        notes: '',
      });
      loadData(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo crear.');
    }
  };

  const handleNotify = async (entry: WaitingListEntry) => {
    if (!token) return;
    try {
      await notifyWaitingListEntry(token, entry.id, scope);
      loadData(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo notificar.');
    }
  };

  const handleAssign = async () => {
    if (!token || !assignTarget) {
      return;
    }
    if (selectedTableIds.length === 0) {
      setAssignError('Selecciona al menos una mesa.');
      return;
    }
    if (assignMode === 'seat' && !selectedServerId) {
      setAssignError('Selecciona un mesero para sentar.');
      return;
    }
    try {
      setAssigning(true);
      setAssignError(null);
      await assignWaitingListTables(
        token,
        assignTarget.id,
        {
          table_ids: selectedTableIds,
          mode: assignMode,
          replace: true,
          server_id: assignMode === 'seat' ? selectedServerId : null,
        },
        scope,
      );
      setShowAssignModal(false);
      setAssignTarget(null);
      setSelectedTableIds([]);
      setSelectedServerId(null);
      setAssignError(null);
      loadData(false);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'No se pudo asignar.';
      setAssignError(message);
    }
    setAssigning(false);
  };

  const handleStatus = async (entry: WaitingListEntry, status: string) => {
    if (!token) return;
    try {
      await updateWaitingListEntry(token, entry.id, {status}, scope);
      loadData(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo actualizar.');
    }
  };

  const handleTableStatus = async (table: DiningTable, status: string) => {
    if (!token) return;
    try {
      await updateDiningTableStatus(
        token,
        table.id,
        status as DiningTable['status'],
        scope,
      );
      loadData(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo cambiar estado.');
    }
  };

  const handleCreateTable = async () => {
    if (!token || !isManager) return;
    try {
      await createDiningTable(token, {
        label: newTable.label.trim(),
        capacity: Number(newTable.capacity) || 2,
        section: newTable.section.trim() || null,
      });
      setShowTableModal(false);
      setNewTable({label: '', capacity: '2', section: ''});
      loadData(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo crear mesa.');
    }
  };

  const handleSettingsSave = async () => {
    if (!token || !settings) return;
    try {
      await updateWaitingListSettings(token, settings, scope);
      setShowSettingsModal(false);
      loadData(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo guardar.');
    }
  };

  const renderWaitingList = () => (
    <View style={styles.section}>
      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Lista de espera</Text>
        <View style={styles.headerActions}>
          <TouchableOpacity
            style={styles.secondaryButton}
            onPress={() => setShowSettingsModal(true)}>
            <Text style={styles.secondaryButtonText}>Config</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={styles.primaryButton}
            onPress={() => setShowEntryModal(true)}>
            <Text style={styles.primaryButtonText}>+ Nuevo</Text>
          </TouchableOpacity>
        </View>
      </View>

      {visibleEntries.length === 0 && !loading ? (
        <Text style={styles.emptyText}>No hay personas en espera.</Text>
      ) : null}

      {visibleEntries.map(entry => (
        <View key={entry.id} style={styles.card}>
          <View style={styles.cardHeader}>
            <View>
              <Text style={styles.cardTitle}>{entry.guest_name}</Text>
              <Text style={styles.cardSubtitle}>
                {entry.party_size} personas • {entry.guest_phone}
              </Text>
            </View>
            <View
              style={[
                styles.statusBadge,
                {backgroundColor: STATUS_COLORS[entry.status] || '#334155'},
              ]}>
              <Text style={styles.statusText}>{entry.status}</Text>
            </View>
          </View>
          <Text style={styles.cardMeta}>
            Espera estimada:{' '}
            {formatMinutes(
              entry.timeclock?.estimated_wait_minutes ??
                entry.quoted_minutes ??
                settings?.default_wait_minutes ??
                15,
            )}{' '}
            min
          </Text>
          {entry.timeclock?.elapsed_wait_minutes !== null &&
          entry.timeclock?.elapsed_wait_minutes !== undefined ? (
            <Text style={styles.cardMeta}>
              En espera: {formatMinutes(entry.timeclock.elapsed_wait_minutes)} min
              {' · '}Restan {formatMinutes(entry.timeclock.remaining_wait_minutes)} min
            </Text>
          ) : null}
          {entry.status === 'seated' && entry.timeclock?.waited_minutes !== null ? (
            <Text style={styles.cardMeta}>
              Esperó {formatMinutes(entry.timeclock.waited_minutes)} min antes de sentarse
            </Text>
          ) : null}
          {entry.tables?.length ? (
            <Text style={styles.cardMeta}>
              Mesas asignadas: {entry.tables.map(t => t.label).join(', ')}
            </Text>
          ) : (
            <Text style={styles.cardMeta}>Sin mesas asignadas</Text>
          )}

          <View style={styles.cardActions}>
            <TouchableOpacity
              style={styles.actionButton}
              onPress={() => {
                setAssignTarget(entry);
                setAssignMode('reserve');
                setSelectedTableIds([]);
                setSelectedServerId(null);
                setAssignError(null);
                setShowAssignModal(true);
              }}>
              <Text style={styles.actionButtonText}>Asignar mesas</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.actionButton}
              onPress={() => handleNotify(entry)}>
              <Text style={styles.actionButtonText}>Notificar</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.actionButton}
              onPress={() => {
                setAssignTarget(entry);
                setAssignMode('seat');
                setSelectedTableIds([]);
                setSelectedServerId(null);
                setAssignError(null);
                setShowAssignModal(true);
              }}>
              <Text style={styles.actionButtonText}>Sentar + asignar mesero</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.actionButtonOutline}
              onPress={() => handleStatus(entry, 'cancelled')}>
              <Text style={styles.actionButtonOutlineText}>Cancelar</Text>
            </TouchableOpacity>
          </View>
        </View>
      ))}
    </View>
  );

  const renderTables = () => (
    <View style={styles.section}>
      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Mesas</Text>
        {isManager ? (
          <TouchableOpacity
            style={styles.primaryButton}
            onPress={() => setShowTableModal(true)}>
            <Text style={styles.primaryButtonText}>+ Mesa</Text>
          </TouchableOpacity>
        ) : null}
      </View>
      <Text style={styles.cardMeta}>Disponibles: {availableTables.length}</Text>
      {tables.map(table => (
        <View key={table.id} style={styles.card}>
          <View style={styles.cardHeader}>
            <View>
              <Text style={styles.cardTitle}>{table.label}</Text>
              <Text style={styles.cardSubtitle}>
                {table.capacity} personas • {table.section ?? 'Sin sección'}
              </Text>
            </View>
            {(() => {
              const badge = resolveTableBadge(table);
              return (
                <View
                  style={[
                    styles.statusBadge,
                    {backgroundColor: badge.color},
                  ]}>
                  <Text style={styles.statusText}>{badge.label}</Text>
                </View>
              );
            })()}
          </View>
          {table.active_assignment?.entry ? (
            <Text style={styles.cardMeta}>
              Asignada a: {table.active_assignment.entry.guest_name}
            </Text>
          ) : null}
          {table.active_session ? (
            <Text style={styles.cardMeta}>
              Ocupada: {formatMinutes(table.active_session.elapsed_minutes)} min
              {' · '}Restan {formatMinutes(table.active_session.remaining_minutes)} min
            </Text>
          ) : null}
          {table.active_session?.server_name ? (
            <Text style={styles.cardMeta}>
              Mesero: {table.active_session.server_name}
            </Text>
          ) : null}
          <View style={styles.cardActions}>
            <TouchableOpacity
              style={styles.actionButton}
              onPress={() => handleTableStatus(table, 'available')}>
              <Text style={styles.actionButtonText}>Disponible</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.actionButton}
              onPress={() => handleTableStatus(table, 'dirty')}>
              <Text style={styles.actionButtonText}>Dirty</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.actionButtonOutline}
              onPress={() => handleTableStatus(table, 'out_of_service')}>
              <Text style={styles.actionButtonOutlineText}>Fuera</Text>
            </TouchableOpacity>
          </View>
        </View>
      ))}
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView
        style={styles.container}
        contentContainerStyle={[
          styles.content,
          {
            paddingTop: insets.top + 8,
            paddingBottom: insets.bottom + 32,
          },
        ]}
        refreshControl={
          <RefreshControl
            tintColor="#fbbf24"
            refreshing={refreshing}
            onRefresh={() => {
              setRefreshing(true);
              loadData(false);
            }}
          />
        }>
        <View style={styles.topBar}>
          <Text style={styles.topTitle}>
            {user?.role === 'host' ? 'Host' : 'Manager'}
          </Text>
          <TouchableOpacity style={styles.logoutButton} onPress={logout}>
            <Text style={styles.logoutText}>Salir</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.toggleRow}>
          <TouchableOpacity
            style={[styles.toggleButton, view === 'waiting' && styles.toggleActive]}
            onPress={() => setView('waiting')}>
            <Text
              style={[
                styles.toggleText,
                view === 'waiting' && styles.toggleTextActive,
              ]}>
              Lista de espera
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.toggleButton, view === 'tables' && styles.toggleActive]}
            onPress={() => setView('tables')}>
            <Text
              style={[
                styles.toggleText,
                view === 'tables' && styles.toggleTextActive,
              ]}>
              Mesas
            </Text>
          </TouchableOpacity>
        </View>

      {error ? <Text style={styles.error}>{error}</Text> : null}
      {loading ? (
        <View style={styles.loading}>
          <ActivityIndicator size="large" color="#fbbf24" />
        </View>
      ) : view === 'waiting' ? (
        renderWaitingList()
      ) : (
        renderTables()
      )}

      <Modal visible={showEntryModal} animationType="slide" transparent>
        <View style={styles.modalBackdrop}>
          <View style={styles.modalCard}>
              <Text style={styles.modalTitle}>Nueva entrada</Text>
            <TextInput
              placeholder="Nombre"
              placeholderTextColor="#94a3b8"
              style={styles.input}
              value={newEntry.guest_name}
              onChangeText={value =>
                setNewEntry(current => ({...current, guest_name: value}))
              }
            />
            <TextInput
              placeholder="Teléfono"
              placeholderTextColor="#94a3b8"
              style={styles.input}
              value={newEntry.guest_phone}
              onChangeText={value =>
                setNewEntry(current => ({...current, guest_phone: value}))
              }
            />
            <TextInput
              placeholder="Email (opcional)"
              placeholderTextColor="#94a3b8"
              style={styles.input}
              value={newEntry.guest_email}
              onChangeText={value =>
                setNewEntry(current => ({...current, guest_email: value}))
              }
            />
            <View style={styles.inlineRow}>
              <TextInput
                placeholder="Personas"
                placeholderTextColor="#94a3b8"
                style={[styles.input, styles.inlineInput]}
                keyboardType="numeric"
                value={newEntry.party_size}
                onChangeText={value =>
                  setNewEntry(current => ({...current, party_size: value}))
                }
              />
              <TextInput
                placeholder="Min. espera"
                placeholderTextColor="#94a3b8"
                style={[styles.input, styles.inlineInput]}
                keyboardType="numeric"
                value={newEntry.quoted_minutes}
                onChangeText={value =>
                  setNewEntry(current => ({...current, quoted_minutes: value}))
                }
              />
            </View>
            <TextInput
              placeholder="Notas"
              placeholderTextColor="#94a3b8"
              style={[styles.input, styles.textArea]}
              value={newEntry.notes}
              multiline
              onChangeText={value =>
                setNewEntry(current => ({...current, notes: value}))
              }
            />
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.secondaryButton}
                onPress={() => setShowEntryModal(false)}>
                <Text style={styles.secondaryButtonText}>Cerrar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.primaryButton}
                onPress={handleCreateEntry}>
                <Text style={styles.primaryButtonText}>Guardar</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      <Modal visible={showAssignModal} animationType="slide" transparent>
        <View style={styles.modalBackdrop}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Asignar mesas</Text>
            <Text style={styles.cardMeta}>
              {assignTarget?.guest_name} • {assignTarget?.party_size} personas
            </Text>
            <View style={styles.inlineRow}>
              <TouchableOpacity
                style={[
                  styles.actionButton,
                  assignMode === 'reserve' && styles.actionButtonActive,
                ]}
                onPress={() => {
                  setAssignMode('reserve');
                  setSelectedServerId(null);
                  setAssignError(null);
                }}>
                <Text style={styles.actionButtonText}>Reservar (mesero la activa)</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[
                  styles.actionButton,
                  assignMode === 'seat' && styles.actionButtonActive,
                ]}
                onPress={() => {
                  setAssignMode('seat');
                  setAssignError(null);
                }}>
                <Text style={styles.actionButtonText}>Sentar + asignar mesero</Text>
              </TouchableOpacity>
            </View>
            {assignMode === 'seat' ? (
              <View>
                <Text style={styles.cardMeta}>Asignar mesero</Text>
                {servers.length === 0 ? (
                  <Text style={styles.emptyText}>No hay meseros activos.</Text>
                ) : (
                  <ScrollView style={styles.serverList}>
                    {servers.map(server => {
                      const selected = selectedServerId === server.id;
                      return (
                        <TouchableOpacity
                          key={server.id}
                          style={[
                            styles.assignItem,
                            selected && styles.assignItemSelected,
                          ]}
                          onPress={() => setSelectedServerId(server.id)}>
                          <Text style={styles.assignItemTitle}>{server.name}</Text>
                          <Text style={styles.assignItemMeta}>{server.email}</Text>
                        </TouchableOpacity>
                      );
                    })}
                  </ScrollView>
                )}
                {assignError ? (
                  <Text style={styles.error}>{assignError}</Text>
                ) : null}
              </View>
            ) : null}
            <ScrollView style={styles.assignList}>
              {tables
                .filter(table =>
                  ['available', 'reserved'].includes(table.status),
                )
                .map(table => {
                  const selected = selectedTableIds.includes(table.id);
                  return (
                    <TouchableOpacity
                      key={table.id}
                      style={[
                        styles.assignItem,
                        selected && styles.assignItemSelected,
                      ]}
                      onPress={() => {
                        setSelectedTableIds(current =>
                          current.includes(table.id)
                            ? current.filter(id => id !== table.id)
                            : [...current, table.id],
                        );
                      }}>
                      <Text style={styles.assignItemTitle}>{table.label}</Text>
                      <Text style={styles.assignItemMeta}>
                        {table.capacity} personas • {table.status}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
            </ScrollView>
            {assignError ? <Text style={styles.error}>{assignError}</Text> : null}
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.secondaryButton}
                onPress={() => setShowAssignModal(false)}>
                <Text style={styles.secondaryButtonText}>Cerrar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.primaryButton}
                onPress={handleAssign}
                disabled={assigning}>
                <Text style={styles.primaryButtonText}>
                  {assigning ? 'Asignando...' : 'Asignar'}
                </Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      <Modal visible={showSettingsModal} animationType="slide" transparent>
        <View style={styles.modalBackdrop}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Configuración</Text>
            <Text style={styles.modalLabel}>Tiempo de espera default (min)</Text>
            <TextInput
              placeholder="15"
              placeholderTextColor="#94a3b8"
              style={styles.input}
              keyboardType="numeric"
              value={settings?.default_wait_minutes?.toString() ?? ''}
              onChangeText={value =>
                setSettings(current =>
                  current
                    ? {...current, default_wait_minutes: Number(value) || 0}
                    : current,
                )
              }
            />
            <Text style={styles.modalLabel}>Minutos para aviso automático</Text>
            <TextInput
              placeholder="10"
              placeholderTextColor="#94a3b8"
              style={styles.input}
              keyboardType="numeric"
              value={settings?.notify_after_minutes?.toString() ?? ''}
              onChangeText={value =>
                setSettings(current =>
                  current
                    ? {...current, notify_after_minutes: Number(value) || 0}
                    : current,
                )
              }
            />
            <Text style={styles.modalLabel}>Mensaje SMS</Text>
            <TextInput
              placeholder="Mensaje SMS"
              placeholderTextColor="#94a3b8"
              style={[styles.input, styles.textArea]}
              value={settings?.notify_message_template ?? ''}
              multiline
              onChangeText={value =>
                setSettings(current =>
                  current ? {...current, notify_message_template: value} : current,
                )
              }
            />
            <View style={styles.switchRow}>
              <Text style={styles.cardMeta}>SMS activo</Text>
              <Switch
                value={settings?.sms_enabled ?? true}
                onValueChange={value =>
                  setSettings(current =>
                    current ? {...current, sms_enabled: value} : current,
                  )
                }
              />
            </View>
            <View style={styles.switchRow}>
              <Text style={styles.cardMeta}>Email activo</Text>
              <Switch
                value={settings?.email_enabled ?? false}
                onValueChange={value =>
                  setSettings(current =>
                    current ? {...current, email_enabled: value} : current,
                  )
                }
              />
            </View>
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.secondaryButton}
                onPress={() => setShowSettingsModal(false)}>
                <Text style={styles.secondaryButtonText}>Cerrar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.primaryButton}
                onPress={handleSettingsSave}>
                <Text style={styles.primaryButtonText}>Guardar</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {isManager ? (
        <Modal visible={showTableModal} animationType="slide" transparent>
          <View style={styles.modalBackdrop}>
            <View style={styles.modalCard}>
              <Text style={styles.modalTitle}>Crear mesa</Text>
              <TextInput
                placeholder="Etiqueta (Mesa 01)"
                placeholderTextColor="#94a3b8"
                style={styles.input}
                value={newTable.label}
                onChangeText={value =>
                  setNewTable(current => ({...current, label: value}))
                }
              />
              <TextInput
                placeholder="Capacidad"
                placeholderTextColor="#94a3b8"
                style={styles.input}
                keyboardType="numeric"
                value={newTable.capacity}
                onChangeText={value =>
                  setNewTable(current => ({...current, capacity: value}))
                }
              />
              <TextInput
                placeholder="Sección"
                placeholderTextColor="#94a3b8"
                style={styles.input}
                value={newTable.section}
                onChangeText={value =>
                  setNewTable(current => ({...current, section: value}))
                }
              />
              <View style={styles.modalActions}>
                <TouchableOpacity
                  style={styles.secondaryButton}
                  onPress={() => setShowTableModal(false)}>
                  <Text style={styles.secondaryButtonText}>Cerrar</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={styles.primaryButton}
                  onPress={handleCreateTable}>
                  <Text style={styles.primaryButtonText}>Guardar</Text>
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </Modal>
      ) : null}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: '#020617'},
  content: {padding: 16, paddingBottom: 48},
  toggleRow: {
    flexDirection: 'row',
    backgroundColor: '#0f172a',
    borderRadius: 16,
    overflow: 'hidden',
    marginBottom: 16,
  },
  topBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  topTitle: {
    color: '#f8fafc',
    fontSize: 16,
    fontWeight: '700',
  },
  logoutButton: {
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 10,
    backgroundColor: '#0f172a',
  },
  logoutText: {
    color: '#f8fafc',
    fontWeight: '600',
  },
  toggleButton: {
    flex: 1,
    paddingVertical: 12,
    alignItems: 'center',
  },
  toggleActive: {backgroundColor: '#fbbf24'},
  toggleText: {color: '#e2e8f0', fontWeight: '600'},
  toggleTextActive: {color: '#0f172a'},
  section: {gap: 12},
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  sectionTitle: {color: '#f8fafc', fontSize: 18, fontWeight: '700'},
  headerActions: {flexDirection: 'row', gap: 8},
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1e293b',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  cardTitle: {color: '#f8fafc', fontSize: 16, fontWeight: '700'},
  cardSubtitle: {color: '#94a3b8', marginTop: 2},
  cardMeta: {color: '#cbd5f5', marginTop: 4},
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 999,
  },
  statusText: {color: '#0f172a', fontWeight: '700', fontSize: 12},
  cardActions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 12,
  },
  primaryButton: {
    backgroundColor: '#fbbf24',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 12,
  },
  primaryButtonText: {color: '#0f172a', fontWeight: '700'},
  secondaryButton: {
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 12,
  },
  secondaryButtonText: {color: '#f8fafc', fontWeight: '600'},
  actionButton: {
    backgroundColor: '#1e293b',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 10,
  },
  actionButtonActive: {backgroundColor: '#fbbf24'},
  actionButtonText: {color: '#f8fafc', fontWeight: '600', fontSize: 12},
  actionButtonOutline: {
    borderWidth: 1,
    borderColor: '#475569',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 10,
  },
  actionButtonOutlineText: {color: '#f8fafc', fontWeight: '600', fontSize: 12},
  loading: {paddingTop: 40},
  emptyText: {color: '#94a3b8', textAlign: 'center', marginTop: 20},
  error: {color: '#f97316', marginBottom: 12},
  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(2, 6, 23, 0.85)',
    justifyContent: 'center',
    padding: 20,
  },
  modalCard: {
    backgroundColor: '#0f172a',
    borderRadius: 16,
    padding: 16,
    gap: 12,
    maxHeight: '90%',
  },
  modalTitle: {color: '#f8fafc', fontSize: 18, fontWeight: '700'},
  modalLabel: {color: '#cbd5f5', fontSize: 12, fontWeight: '600'},
  input: {
    backgroundColor: '#111827',
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 10,
    color: '#f8fafc',
    borderWidth: 1,
    borderColor: '#1f2937',
  },
  textArea: {minHeight: 80, textAlignVertical: 'top'},
  inlineRow: {flexDirection: 'row', gap: 8},
  inlineInput: {flex: 1},
  modalActions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 8,
  },
  assignList: {maxHeight: 220},
  serverList: {maxHeight: 160, marginTop: 8},
  assignItem: {
    padding: 10,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#1f2937',
    marginBottom: 8,
  },
  assignItemSelected: {
    backgroundColor: 'rgba(251, 191, 36, 0.15)',
    borderColor: '#fbbf24',
  },
  assignItemTitle: {color: '#f8fafc', fontWeight: '700'},
  assignItemMeta: {color: '#94a3b8', marginTop: 2},
  switchRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
});

export default ManagerHostScreen;
