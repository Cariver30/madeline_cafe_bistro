import React, {useMemo, useState} from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
  useWindowDimensions,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {PosStackParamList} from '../../navigation/posTypes';
import {usePosTickets} from '../../context/PosTicketsContext';

const PosNewTicketScreen = ({
  navigation,
}: NativeStackScreenProps<PosStackParamList, 'PosNewTicket'>) => {
  const {createTicket, actionState, error} = usePosTickets();
  const [type, setType] = useState<'walkin' | 'phone'>('walkin');
  const [guestName, setGuestName] = useState('');
  const [guestEmail, setGuestEmail] = useState('');
  const [guestPhone, setGuestPhone] = useState('');
  const [partySize, setPartySize] = useState('1');
  const {width} = useWindowDimensions();
  const isTablet = width >= 768;
  const isSubmitting = actionState.creatingTicket;

  const isValid = useMemo(() => {
    if (!partySize || Number(partySize) < 1) return false;
    if (type === 'phone' && !guestPhone.trim()) return false;
    return true;
  }, [partySize, type, guestPhone]);

  const handleCreate = async () => {
    if (!isValid || isSubmitting) return;
    const ticket = await createTicket({
      type,
      guest_name: guestName.trim() || undefined,
      guest_email: guestEmail.trim() || undefined,
      guest_phone: guestPhone.trim() || undefined,
      party_size: Number(partySize),
    });
    if (ticket) {
      navigation.replace('PosTicket', {ticketId: ticket.id});
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <View style={[styles.card, isTablet && styles.cardTablet]}>
        <Text style={styles.title}>Nuevo ticket</Text>
        <Text style={styles.subtitle}>Walk-in o teléfono</Text>

        <View style={styles.toggleRow}>
          <TouchableOpacity
            style={[
              styles.toggleButton,
              type === 'walkin' && styles.toggleActive,
            ]}
            onPress={() => setType('walkin')}>
            <Text
              style={[
                styles.toggleText,
                type === 'walkin' && styles.toggleTextActive,
              ]}>
              Walk-in
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[
              styles.toggleButton,
              type === 'phone' && styles.toggleActive,
            ]}
            onPress={() => setType('phone')}>
            <Text
              style={[
                styles.toggleText,
                type === 'phone' && styles.toggleTextActive,
              ]}>
              Teléfono
            </Text>
          </TouchableOpacity>
        </View>

        <TextInput
          style={styles.input}
          placeholder="Nombre (opcional)"
          placeholderTextColor="#94a3b8"
          value={guestName}
          onChangeText={setGuestName}
        />
        <TextInput
          style={styles.input}
          placeholder="Email (opcional)"
          placeholderTextColor="#94a3b8"
          value={guestEmail}
          onChangeText={setGuestEmail}
          keyboardType="email-address"
          autoCapitalize="none"
        />
        <TextInput
          style={[styles.input, type === 'phone' && styles.inputRequired]}
          placeholder="Teléfono"
          placeholderTextColor="#94a3b8"
          value={guestPhone}
          onChangeText={setGuestPhone}
          keyboardType="phone-pad"
        />
        <TextInput
          style={styles.input}
          placeholder="Personas"
          placeholderTextColor="#94a3b8"
          value={partySize}
          onChangeText={setPartySize}
          keyboardType="number-pad"
        />

        {error ? <Text style={styles.error}>{error}</Text> : null}

        <TouchableOpacity
          style={[
            styles.primaryButton,
            (!isValid || isSubmitting) && styles.buttonDisabled,
          ]}
          onPress={handleCreate}
          disabled={!isValid || isSubmitting}>
          {isSubmitting ? (
            <ActivityIndicator color="#0f172a" />
          ) : (
            <Text style={styles.primaryText}>Crear ticket</Text>
          )}
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#020617',
    padding: 20,
    justifyContent: 'center',
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 24,
    padding: 20,
    borderWidth: 1,
    borderColor: '#1e293b',
    gap: 12,
  },
  cardTablet: {
    maxWidth: 640,
    alignSelf: 'center',
    width: '100%',
  },
  title: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
  subtitle: {
    color: '#94a3b8',
    fontSize: 12,
  },
  toggleRow: {
    flexDirection: 'row',
    gap: 10,
  },
  toggleButton: {
    flex: 1,
    paddingVertical: 10,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    alignItems: 'center',
  },
  toggleActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  toggleText: {
    color: '#94a3b8',
    fontWeight: '600',
  },
  toggleTextActive: {
    color: '#0f172a',
  },
  input: {
    backgroundColor: '#1e293b',
    borderRadius: 16,
    paddingHorizontal: 16,
    paddingVertical: 10,
    color: '#f8fafc',
    fontSize: 14,
  },
  inputRequired: {
    borderWidth: 2,
    borderColor: '#fbbf24',
  },
  primaryButton: {
    backgroundColor: '#22c55e',
    borderRadius: 999,
    paddingVertical: 12,
    alignItems: 'center',
  },
  buttonDisabled: {
    opacity: 0.35,
  },
  primaryText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  error: {
    color: '#fb7185',
    fontSize: 12,
  },
});

export default PosNewTicketScreen;
