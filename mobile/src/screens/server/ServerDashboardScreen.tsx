import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useAuth} from '../../context/AuthContext';
import {getServerDashboardSummary, getServerSummary} from '../../services/api';
import {ServerDashboardSummary, SummaryResponse} from '../../types';

const formatCurrency = (value: number) => `$${value.toFixed(2)}`;

const emptySummary: ServerDashboardSummary = {
  sales_total: 0,
  tips_total: 0,
  orders_count: 0,
  tables_closed: 0,
  active_tables: 0,
};

const ServerDashboardScreen = () => {
  const {token, user} = useAuth();
  const [summary, setSummary] = useState<ServerDashboardSummary>(emptySummary);
  const [loyalty, setLoyalty] = useState<SummaryResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadSummary = useCallback(
    async (showLoader = true) => {
      if (!token) {
        setSummary(emptySummary);
        setLoyalty(null);
        setLoading(false);
        return;
      }
      try {
        if (showLoader) {
          setLoading(true);
        }
        const [dashboardResult, loyaltyResult] = await Promise.allSettled([
          getServerDashboardSummary(token),
          getServerSummary(token),
        ]);
        if (dashboardResult.status === 'fulfilled') {
          setSummary(dashboardResult.value);
        }
        if (loyaltyResult.status === 'fulfilled') {
          setLoyalty(loyaltyResult.value);
        }
        if (
          dashboardResult.status === 'rejected' &&
          loyaltyResult.status === 'rejected'
        ) {
          throw dashboardResult.reason;
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
    [token],
  );

  useEffect(() => {
    loadSummary();
  }, [loadSummary]);

  const onRefresh = () => {
    setRefreshing(true);
    loadSummary(false);
  };

  const activeVisit = loyalty?.active_visit ?? null;
  const previousVisit = useMemo(() => {
    const visits = loyalty?.recent_visits ?? [];
    const sorted = [...visits].sort((a, b) => {
      const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
      const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
      return dateB - dateA;
    });
    return (
      sorted.find(
        visit =>
          visit.status === 'confirmed' && visit.id !== activeVisit?.id,
      ) ?? null
    );
  }, [loyalty, activeVisit]);

  return (
    <ScrollView
      style={styles.container}
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
        <Text style={styles.subtitle}>Resumen de hoy</Text>
      </View>

      {loading ? (
        <View style={styles.loader}>
          <ActivityIndicator color="#fbbf24" />
        </View>
      ) : (
        <>
          {error ? <Text style={styles.error}>{error}</Text> : null}

          <View style={styles.card}>
            <Text style={styles.cardLabel}>Ventas del dia</Text>
            <Text style={styles.cardValue}>
              {formatCurrency(summary.sales_total)}
            </Text>
          </View>

          <View style={styles.card}>
            <Text style={styles.cardLabel}>Propinas del dia</Text>
            <Text style={styles.cardValue}>
              {formatCurrency(summary.tips_total)}
            </Text>
          </View>

          <View style={styles.grid}>
            <View style={styles.smallCard}>
              <Text style={styles.smallLabel}>Ordenes confirmadas</Text>
              <Text style={styles.smallValue}>{summary.orders_count}</Text>
            </View>
            <View style={styles.smallCard}>
              <Text style={styles.smallLabel}>Mesas cerradas</Text>
              <Text style={styles.smallValue}>{summary.tables_closed}</Text>
            </View>
            <View style={styles.smallCard}>
              <Text style={styles.smallLabel}>Mesas activas</Text>
              <Text style={styles.smallValue}>{summary.active_tables}</Text>
            </View>
          </View>

          <View style={styles.card}>
            <Text style={styles.cardLabel}>Fidelidad en curso</Text>
            {activeVisit ? (
              <>
                <Text style={styles.cardValueSmall}>{activeVisit.name}</Text>
                <Text style={styles.cardMeta}>
                  {activeVisit.email} · {activeVisit.phone}
                </Text>
              </>
            ) : (
              <Text style={styles.cardMeta}>No hay QR activo.</Text>
            )}
          </View>

          <View style={styles.card}>
            <Text style={styles.cardLabel}>Ultima fidelidad confirmada</Text>
            {previousVisit ? (
              <>
                <Text style={styles.cardValueSmall}>{previousVisit.name}</Text>
                <Text style={styles.cardMeta}>
                  {previousVisit.email} · {previousVisit.phone}
                </Text>
                <Text style={styles.cardMeta}>
                  {previousVisit.points} pts
                </Text>
              </>
            ) : (
              <Text style={styles.cardMeta}>
                Aún no hay fidelidad confirmada.
              </Text>
            )}
          </View>
        </>
      )}
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
    borderRadius: 20,
    padding: 18,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 6,
  },
  cardLabel: {
    color: '#94a3b8',
    fontSize: 12,
    textTransform: 'uppercase',
    letterSpacing: 1.6,
  },
  cardValue: {
    color: '#fbbf24',
    fontSize: 22,
    fontWeight: '800',
  },
  cardValueSmall: {
    color: '#f8fafc',
    fontSize: 16,
    fontWeight: '700',
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  smallCard: {
    backgroundColor: '#0f172a',
    borderRadius: 18,
    padding: 14,
    borderWidth: 1,
    borderColor: '#1f2937',
    flexBasis: '48%',
    gap: 6,
  },
  smallLabel: {
    color: '#94a3b8',
    fontSize: 11,
    textTransform: 'uppercase',
    letterSpacing: 1.4,
  },
  smallValue: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
});

export default ServerDashboardScreen;
