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
} from 'react-native';
import {useAuth} from '../context/AuthContext';

const LoginScreen = () => {
  const {login, authLoading, authError, clearError} = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [localError, setLocalError] = useState<string | null>(null);

  const disabled = useMemo(
    () => !email.trim() || !password.trim() || authLoading,
    [email, password, authLoading],
  );

  const handleSubmit = async () => {
    if (!email || !password) {
      setLocalError('Completa correo y contraseña.');
      return;
    }
    setLocalError(null);
    clearError();
    try {
      await login(email.trim(), password);
    } catch {
      // el mensaje ya se gestiona en el contexto
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      style={styles.container}>
      <View style={styles.card}>
        <Text style={styles.heading}>Panel móvil Kfeina</Text>
        <Text style={styles.subheading}>
          Ingresa tus credenciales del panel administrativo.
        </Text>

        <TextInput
          value={email}
          onChangeText={setEmail}
          autoCapitalize="none"
          keyboardType="email-address"
          placeholder="Correo corporativo"
          placeholderTextColor="#94a3b8"
          style={styles.input}
        />
        <TextInput
          value={password}
          onChangeText={setPassword}
          placeholder="Contraseña"
          placeholderTextColor="#94a3b8"
          secureTextEntry
          style={styles.input}
        />

        {(authError || localError) && (
          <Text style={styles.errorText}>{authError ?? localError}</Text>
        )}

        <TouchableOpacity
          style={[styles.button, disabled && styles.buttonDisabled]}
          onPress={handleSubmit}
          disabled={disabled}>
          {authLoading ? (
            <ActivityIndicator color="#0f172a" />
          ) : (
            <Text style={styles.buttonText}>Entrar</Text>
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
    padding: 24,
    justifyContent: 'center',
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 28,
    padding: 24,
    borderWidth: 1,
    borderColor: '#1e293b',
    gap: 14,
  },
  heading: {
    fontSize: 22,
    color: '#f8fafc',
    fontWeight: '700',
  },
  subheading: {
    fontSize: 14,
    color: '#94a3b8',
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
    marginTop: 8,
  },
  buttonDisabled: {
    opacity: 0.7,
  },
  buttonText: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
  },
  errorText: {
    color: '#fb7185',
    fontSize: 14,
    textAlign: 'center',
  },
});

export default LoginScreen;
