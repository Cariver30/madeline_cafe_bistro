import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  Modal,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {useFocusEffect} from '@react-navigation/native';
import {useAuth} from '../../context/AuthContext';
import {useServerSessions} from '../../context/ServerSessionsContext';
import {createTableSession, getServerDiningTables} from '../../services/api';
import {DiningTable} from '../../types';
import {ServerStackParamList} from '../../navigation/serverTypes';

type Props = NativeStackScreenProps<ServerStackParamList, 'NewTable'>;

const NewTableScreen = ({navigation}: Props) => {
  const {token} = useAuth();
  const {loadSessions} = useServerSessions();
  const [creating, setCreating] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [tablesLoading, setTablesLoading] = useState(false);
  const [tables, setTables] = useState<DiningTable[]>([]);
  const [showTablePicker, setShowTablePicker] = useState(false);
  const [selectedTableIds, setSelectedTableIds] = useState<number[]>([]);
  const [combineTables, setCombineTables] = useState(false);
  const [form, setForm] = useState({
    party_size: '',
    guest_name: '',
    guest_email: '',
    guest_phone: '',
    order_mode: 'table' as 'table' | 'traditional',
    group_name: '',
  });


  const loadTables = useCallback(async () => {
    if (!token) {
      return;
    }
    setTablesLoading(true);
    try {
      const result = await getServerDiningTables(token);
      setTables(result);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudieron cargar mesas.');
    } finally {
      setTablesLoading(false);
    }
  }, [token]);

  useFocusEffect(
    useCallback(() => {
      loadTables();
    }, [loadTables]),
  );

  const handleCreate = async () => {
    if (!token) {
      return;
    }
    if (
      selectedTableIds.length === 0 ||
      !form.party_size ||
      !form.guest_name.trim() ||
      !form.guest_email.trim() ||
      !form.guest_phone.trim()
    ) {
      setError('Completa todos los campos.');
      return;
    }
    if (selectedTableIds.length > 1 && !form.group_name.trim()) {
      setError('Ingresa el nombre del grupo.');
      return;
    }
    setCreating(true);
    setError(null);
    try {
      const created = await createTableSession(token, {
        table_ids: selectedTableIds,
        party_size: Number(form.party_size),
        guest_name: form.guest_name.trim(),
        guest_email: form.guest_email.trim(),
        guest_phone: form.guest_phone.trim(),
        order_mode: form.order_mode,
        group_name: selectedTableIds.length > 1 ? form.group_name.trim() : undefined,
      });
      await loadSessions(false);
      navigation.replace('TableDetail', {sessionId: created.id});
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo crear.');
    } finally {
      setCreating(false);
    }
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      <View style={styles.card}>
        <Text style={styles.heading}>Nueva mesa</Text>
        <Text style={styles.subheading}>
          Define si el cliente ordena o si el mesero toma la orden.
        </Text>

        <View style={styles.modeRow}>
          <TouchableOpacity
            style={[
              styles.modeOption,
              form.order_mode === 'traditional' && styles.modeOptionActive,
            ]}
            onPress={() =>
              setForm(prev => ({...prev, order_mode: 'traditional'}))
            }>
            <Text
              style={[
                styles.modeOptionText,
                form.order_mode === 'traditional' &&
                  styles.modeOptionTextActive,
              ]}>
              Tradicional
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[
              styles.modeOption,
              form.order_mode === 'table' && styles.modeOptionActive,
            ]}
            onPress={() => setForm(prev => ({...prev, order_mode: 'table'}))}>
            <Text
              style={[
                styles.modeOptionText,
                form.order_mode === 'table' && styles.modeOptionTextActive,
              ]}>
              Mesa ordena
            </Text>
          </TouchableOpacity>
        </View>

        <TouchableOpacity
          style={styles.selector}
          onPress={() => setShowTablePicker(true)}
          disabled={tablesLoading}
          >
          <Text style={styles.selectorText}>
            {selectedTableIds.length
              ? `Mesas: ${tables
                  .filter(t => selectedTableIds.includes(t.id))
                  .sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
                  .map(t => t.label)
                  .join(' + ')}`
              : 'Seleccionar mesas'}
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[
            styles.combineToggle,
            combineTables && styles.combineToggleActive,
          ]}
          onPress={() => {
            setCombineTables(prev => {
              const next = !prev;
              if (!next) {
                setSelectedTableIds(current =>
                  current.length ? [current[0]] : [],
                );
                setForm(currentForm => ({...currentForm, group_name: ''}));
              }
              return next;
            });
          }}>
          <Text
            style={[
              styles.combineToggleText,
              combineTables && styles.combineToggleTextActive,
            ]}>
            Unir mesas
          </Text>
        </TouchableOpacity>
        {selectedTableIds.length > 1 ? (
          <TextInput
            style={styles.input}
            placeholder="Nombre del grupo"
            placeholderTextColor="#94a3b8"
            value={form.group_name}
            onChangeText={text => setForm(prev => ({...prev, group_name: text}))}
          />
        ) : null}
        <TextInput
          style={styles.input}
          placeholder="Personas"
          placeholderTextColor="#94a3b8"
          keyboardType="number-pad"
          value={form.party_size}
          onChangeText={text => setForm(prev => ({...prev, party_size: text}))}
        />
        <TextInput
          style={styles.input}
          placeholder="Nombre"
          placeholderTextColor="#94a3b8"
          value={form.guest_name}
          onChangeText={text => setForm(prev => ({...prev, guest_name: text}))}
        />
        <TextInput
          style={styles.input}
          placeholder="Correo"
          placeholderTextColor="#94a3b8"
          keyboardType="email-address"
          autoCapitalize="none"
          value={form.guest_email}
          onChangeText={text => setForm(prev => ({...prev, guest_email: text}))}
        />
        <TextInput
          style={styles.input}
          placeholder="Teléfono"
          placeholderTextColor="#94a3b8"
          value={form.guest_phone}
          onChangeText={text => setForm(prev => ({...prev, guest_phone: text}))}
        />

        {error ? <Text style={styles.error}>{error}</Text> : null}

        <TouchableOpacity
          style={[styles.button, creating && styles.buttonDisabled]}
          onPress={handleCreate}
          disabled={creating}>
          {creating ? (
            <ActivityIndicator color="#0f172a" />
          ) : (
            <Text style={styles.buttonText}>Generar QR</Text>
          )}
        </TouchableOpacity>
      </View>

      <Modal visible={showTablePicker} animationType="slide" transparent>
        <View style={styles.modalBackdrop}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Selecciona mesa</Text>
            <ScrollView style={styles.modalList}>
              {tables
                .map(table => {
                  const isSelectable =
                    table.status === 'available' ||
                    table.status === 'reserved' ||
                    (!combineTables &&
                      table.status === 'occupied' &&
                      !table.active_session);
                  return (
                    <TouchableOpacity
                      key={table.id}
                      style={[
                        styles.modalItem,
                        selectedTableIds.includes(table.id) &&
                          styles.modalItemSelected,
                        !isSelectable && styles.modalItemDisabled,
                      ]}
                      onPress={() => {
                        if (!isSelectable) {
                          return;
                        }
                        if (combineTables) {
                          setSelectedTableIds(current => {
                            if (current.includes(table.id)) {
                              return current.filter(id => id !== table.id);
                            }
                            return [...current, table.id];
                          });
                          return;
                        }
                        setSelectedTableIds([table.id]);
                        setShowTablePicker(false);
                      }}>
                      <Text style={styles.modalItemText}>
                        {table.label} • {table.capacity} personas •{' '}
                        {table.status === 'occupied'
                          ? table.active_session
                            ? `ocupada (${table.active_session.server_name ?? 'mesero'})`
                            : 'ocupada (sin mesero)'
                          : table.status}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
            </ScrollView>
            <View style={styles.modalActions}>
              {combineTables ? (
                <TouchableOpacity
                  style={styles.secondaryButton}
                  onPress={() => setShowTablePicker(false)}
                  >
                  <Text style={styles.secondaryButtonText}>Listo</Text>
                </TouchableOpacity>
              ) : null}
              <TouchableOpacity
                style={styles.secondaryButton}
                onPress={() => setShowTablePicker(false)}
                >
                <Text style={styles.secondaryButtonText}>Cerrar</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
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
    gap: 12,
  },
  heading: {
    fontSize: 18,
    fontWeight: '700',
    color: '#f8fafc',
  },
  subheading: {
    fontSize: 14,
    color: '#94a3b8',
  },
  modeRow: {
    flexDirection: 'row',
    gap: 8,
  },
  modeOption: {
    flex: 1,
    borderRadius: 16,
    paddingVertical: 10,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#334155',
    backgroundColor: '#0b1220',
  },
  modeOptionActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  modeOptionText: {
    color: '#cbd5f5',
    fontWeight: '600',
    fontSize: 13,
  },
  modeOptionTextActive: {
    color: '#0f172a',
  },
  selector: {
    borderWidth: 1,
    borderColor: '#1e293b',
    padding: 12,
    borderRadius: 14,
    backgroundColor: '#0b1220',
  },
  selectorText: {
    color: '#f8fafc',
    fontWeight: '600',
  },
  combineToggle: {
    borderWidth: 1,
    borderColor: '#334155',
    borderRadius: 14,
    paddingVertical: 10,
    alignItems: 'center',
    backgroundColor: '#0b1220',
  },
  combineToggleActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  combineToggleText: {
    color: '#cbd5f5',
    fontWeight: '700',
    fontSize: 13,
  },
  combineToggleTextActive: {
    color: '#0f172a',
  },
  input: {
    backgroundColor: '#1e293b',
    borderRadius: 18,
    paddingHorizontal: 16,
    paddingVertical: 12,
    color: '#f8fafc',
    fontSize: 16,
  },
  button: {
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingVertical: 14,
    alignItems: 'center',
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  inputDisabled: {
    opacity: 0.5,
  },
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
    maxHeight: '80%',
  },
  modalTitle: {
    color: '#f8fafc',
    fontSize: 16,
    fontWeight: '700',
  },
  modalList: {
    maxHeight: 300,
  },
  modalActions: {
    gap: 8,
  },
  modalItem: {
    padding: 10,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#1f2937',
    marginBottom: 8,
  },
  modalItemSelected: {
    borderColor: '#fbbf24',
    backgroundColor: 'rgba(251, 191, 36, 0.15)',
  },
  modalItemDisabled: {
    opacity: 0.45,
  },
  modalItemText: {
    color: '#f8fafc',
    fontWeight: '600',
  },
  secondaryButton: {
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 12,
    alignItems: 'center',
  },
  secondaryButtonText: {
    color: '#f8fafc',
    fontWeight: '600',
  },
  buttonText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  error: {
    color: '#fb7185',
    fontSize: 14,
  },
});

export default NewTableScreen;
