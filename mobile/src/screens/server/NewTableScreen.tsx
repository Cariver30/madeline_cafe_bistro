import React, {useState} from 'react';
import {
  ActivityIndicator,
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
import {createTableSession} from '../../services/api';
import {ServerStackParamList} from '../../navigation/serverTypes';

type Props = NativeStackScreenProps<ServerStackParamList, 'NewTable'>;

const NewTableScreen = ({navigation}: Props) => {
  const {token} = useAuth();
  const {loadSessions} = useServerSessions();
  const [creating, setCreating] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [form, setForm] = useState({
    table_label: '',
    party_size: '',
    guest_name: '',
    guest_email: '',
    guest_phone: '',
    order_mode: 'table' as 'table' | 'traditional',
  });

  const handleCreate = async () => {
    if (!token) {
      return;
    }
    if (
      !form.table_label.trim() ||
      !form.party_size ||
      !form.guest_name.trim() ||
      !form.guest_email.trim() ||
      !form.guest_phone.trim()
    ) {
      setError('Completa todos los campos.');
      return;
    }
    setCreating(true);
    setError(null);
    try {
      const created = await createTableSession(token, {
        table_label: form.table_label.trim(),
        party_size: Number(form.party_size),
        guest_name: form.guest_name.trim(),
        guest_email: form.guest_email.trim(),
        guest_phone: form.guest_phone.trim(),
        order_mode: form.order_mode,
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

        <TextInput
          style={styles.input}
          placeholder="Mesa"
          placeholderTextColor="#94a3b8"
          value={form.table_label}
          onChangeText={text =>
            setForm(prev => ({...prev, table_label: text}))
          }
        />
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
