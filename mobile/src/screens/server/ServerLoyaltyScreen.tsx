import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Image,
  KeyboardAvoidingView,
  Platform,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {useAuth} from '../../context/AuthContext';
import {createVisit, getServerSummary} from '../../services/api';
import {LoyaltyRewardSummary, SummaryResponse} from '../../types';

const buildQrUrl = (qrUrl: string) =>
  `https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=${encodeURIComponent(
    qrUrl,
  )}`;

const ServerLoyaltyScreen = () => {
  const {token, user} = useAuth();
  const [summary, setSummary] = useState<SummaryResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const isValid =
    name.trim().length > 0 &&
    email.trim().length > 0 &&
    phone.trim().length > 0;

  const loadSummary = useCallback(
    async (showLoader = true) => {
      if (!token) {
        setSummary(null);
        setLoading(false);
        return;
      }
      try {
        if (showLoader) {
          setLoading(true);
        }
        const data = await getServerSummary(token);
        setSummary(data);
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
    [token],
  );

  useEffect(() => {
    loadSummary();
  }, [loadSummary]);

  const onRefresh = () => {
    setRefreshing(true);
    loadSummary(false);
  };

  const handleCreate = async () => {
    if (!token || submitting) {
      return;
    }
    setSubmitting(true);
    setError(null);
    try {
      await createVisit(token, {
        name: name.trim(),
        email: email.trim(),
        phone: phone.trim(),
      });
      setName('');
      setEmail('');
      setPhone('');
      await loadSummary(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo crear.');
    } finally {
      setSubmitting(false);
    }
  };

  const rewards = useMemo<LoyaltyRewardSummary[]>(
    () => summary?.rewards ?? [],
    [summary],
  );
  const hasActiveVisit = Boolean(summary?.active_visit);
  const qrImageUrl = summary?.qr_url ? buildQrUrl(summary.qr_url) : null;

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={
          <RefreshControl
            tintColor="#fbbf24"
            refreshing={refreshing}
            onRefresh={onRefresh}
          />
        }>
        <View style={styles.header}>
          <Text style={styles.title}>Hola {user?.name}</Text>
          <Text style={styles.subtitle}>Programa de fidelidad</Text>
        </View>

        {loading ? (
          <View style={styles.loader}>
            <ActivityIndicator color="#fbbf24" />
          </View>
        ) : (
          <>
            {error ? <Text style={styles.error}>{error}</Text> : null}

            <View style={styles.card}>
              <Text style={styles.cardHeading}>Nueva visita</Text>
              <Text style={styles.cardMeta}>
                Cada confirmación suma {summary?.points_per_visit ?? 0} puntos.
              </Text>
              <View style={styles.form}>
                <TextInput
                  style={styles.input}
                  placeholder="Nombre"
                  placeholderTextColor="#94a3b8"
                  value={name}
                  onChangeText={setName}
                />
                <TextInput
                  style={styles.input}
                  placeholder="Correo"
                  placeholderTextColor="#94a3b8"
                  value={email}
                  onChangeText={setEmail}
                  keyboardType="email-address"
                  autoCapitalize="none"
                />
                <TextInput
                  style={styles.input}
                  placeholder="Teléfono"
                  placeholderTextColor="#94a3b8"
                  value={phone}
                  onChangeText={setPhone}
                  keyboardType="phone-pad"
                />
                <TouchableOpacity
                  style={[
                    styles.primaryButton,
                    (!isValid || submitting || hasActiveVisit) &&
                      styles.buttonDisabled,
                  ]}
                  onPress={handleCreate}
                  disabled={!isValid || submitting || hasActiveVisit}>
                  {submitting ? (
                    <ActivityIndicator color="#0f172a" />
                  ) : (
                    <Text style={styles.primaryText}>
                      {hasActiveVisit ? 'QR activo' : 'Generar QR'}
                    </Text>
                  )}
                </TouchableOpacity>
              </View>
            </View>

            <View style={styles.card}>
              <Text style={styles.cardHeading}>QR activo</Text>
              {qrImageUrl ? (
                <Image source={{uri: qrImageUrl}} style={styles.qrImage} />
              ) : (
                <View style={styles.qrPlaceholder}>
                  <Text style={styles.qrPlaceholderText}>
                    Genera un código y aparecerá aquí.
                  </Text>
                </View>
              )}
              <Text style={styles.cardMeta}>
                Comparte este QR para que el cliente confirme sus datos.
              </Text>
            </View>

            {summary?.terms ? (
              <View style={styles.card}>
                <Text style={styles.cardHeading}>Instrucciones</Text>
                <Text style={styles.cardBody}>{summary.terms}</Text>
              </View>
            ) : null}

            <View style={styles.card}>
              <Text style={styles.cardHeading}>Recompensas activas</Text>
              {rewards.length ? (
                rewards.map(reward => (
                  <View key={reward.id} style={styles.rewardRow}>
                    <View style={styles.rewardInfo}>
                      <Text style={styles.rewardTitle}>{reward.title}</Text>
                      {reward.description ? (
                        <Text style={styles.rewardDesc}>
                          {reward.description}
                        </Text>
                      ) : null}
                    </View>
                    <Text style={styles.rewardPoints}>
                      {reward.points_required} pts
                    </Text>
                  </View>
                ))
              ) : (
                <Text style={styles.cardMeta}>
                  No hay recompensas configuradas.
                </Text>
              )}
            </View>

            <View style={styles.card}>
              <Text style={styles.cardHeading}>Visitas recientes</Text>
              {summary?.recent_visits?.length ? (
                summary.recent_visits.map(visit => (
                  <View key={visit.id} style={styles.visitRow}>
                    <View>
                      <Text style={styles.visitName}>{visit.name}</Text>
                      <Text style={styles.visitMeta}>
                        {visit.email} · {visit.phone}
                      </Text>
                    </View>
                    <Text style={styles.visitPoints}>
                      {visit.points} pts
                    </Text>
                  </View>
                ))
              ) : (
                <Text style={styles.cardMeta}>
                  Aún no hay visitas registradas.
                </Text>
              )}
            </View>
          </>
        )}
      </ScrollView>
    </KeyboardAvoidingView>
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
  header: {
    gap: 6,
  },
  title: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
  subtitle: {
    color: '#94a3b8',
    fontSize: 13,
  },
  loader: {
    paddingVertical: 40,
    alignItems: 'center',
  },
  error: {
    color: '#fb7185',
    fontSize: 13,
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 24,
    padding: 18,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 10,
  },
  cardHeading: {
    color: '#f8fafc',
    fontSize: 15,
    fontWeight: '700',
  },
  cardMeta: {
    color: '#94a3b8',
    fontSize: 12,
  },
  cardBody: {
    color: '#e2e8f0',
    fontSize: 13,
    lineHeight: 18,
  },
  form: {
    gap: 10,
  },
  input: {
    backgroundColor: '#1e293b',
    borderRadius: 14,
    paddingHorizontal: 14,
    paddingVertical: 10,
    color: '#f8fafc',
  },
  primaryButton: {
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingVertical: 12,
    alignItems: 'center',
  },
  primaryText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  qrImage: {
    width: '100%',
    aspectRatio: 1,
    borderRadius: 16,
    backgroundColor: '#020617',
  },
  qrPlaceholder: {
    width: '100%',
    aspectRatio: 1,
    borderRadius: 16,
    backgroundColor: '#020617',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 20,
  },
  qrPlaceholderText: {
    color: '#64748b',
    textAlign: 'center',
  },
  rewardRow: {
    borderWidth: 1,
    borderColor: '#1f2937',
    borderRadius: 16,
    padding: 12,
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 10,
  },
  rewardInfo: {
    flex: 1,
    gap: 4,
  },
  rewardTitle: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 14,
  },
  rewardDesc: {
    color: '#94a3b8',
    fontSize: 12,
  },
  rewardPoints: {
    color: '#fbbf24',
    fontWeight: '700',
    fontSize: 14,
  },
  visitRow: {
    borderWidth: 1,
    borderColor: '#1f2937',
    borderRadius: 16,
    padding: 12,
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 10,
  },
  visitName: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 14,
  },
  visitMeta: {
    color: '#94a3b8',
    fontSize: 12,
  },
  visitPoints: {
    color: '#38bdf8',
    fontWeight: '700',
  },
});

export default ServerLoyaltyScreen;
