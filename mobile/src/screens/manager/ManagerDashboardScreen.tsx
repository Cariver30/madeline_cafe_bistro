import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';
import {useAuth} from '../../context/AuthContext';
import {
  getManagerDashboard,
  getManagerOperations,
  toggleServer,
} from '../../services/api';
import {ManagerOpsSummary, ManagerSummary} from '../../types';

const formatCurrency = (value: number) => `$${value.toFixed(2)}`;

const ManagerDashboardScreen = () => {
  const {token, user, logout} = useAuth();
  const [summary, setSummary] = useState<ManagerSummary | null>(null);
  const [operations, setOperations] = useState<ManagerOpsSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [togglingId, setTogglingId] = useState<number | null>(null);

  const loadData = useCallback(
    async (showLoader = true) => {
      if (!token) {
        return;
      }
      try {
        if (showLoader) {
          setLoading(true);
        }
        const [dashboardResult, operationsResult] = await Promise.allSettled([
          getManagerDashboard(token),
          getManagerOperations(token),
        ]);
        if (dashboardResult.status === 'fulfilled') {
          setSummary(dashboardResult.value);
        }
        if (operationsResult.status === 'fulfilled') {
          setOperations(operationsResult.value);
        }
        if (
          dashboardResult.status === 'rejected' &&
          operationsResult.status === 'rejected'
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

  useFocusEffect(
    useCallback(() => {
      loadData();
    }, [loadData]),
  );

  const handleToggle = async (serverId: number) => {
    if (!token) {
      return;
    }
    setTogglingId(serverId);
    try {
      await toggleServer(token, serverId);
      await loadData(false);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'No se pudo actualizar el mesero.',
      );
    } finally {
      setTogglingId(null);
    }
  };

  const opsTotals = operations?.totals;
  const servers = operations?.servers ?? [];
  const salesByChannel = operations?.sales_by_channel ?? [];
  const topItems = operations?.top_items ?? [];
  const onlineServers = servers.filter(server => server.is_online);

  const formatDelta = (delta: number | null | undefined) => {
    if (delta === null || delta === undefined) {
      return 'Sin comparación';
    }
    const sign = delta >= 0 ? '+' : '';
    return `${sign}${delta.toFixed(1)}% vs ayer`;
  };

  const formatLastSeen = (value: string | null) => {
    if (!value) {
      return 'Sin actividad';
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return 'Sin actividad';
    }
    return date.toLocaleTimeString('es-PR', {
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const formatChannel = (channel: string) => {
    if (channel === 'walkin') return 'Walk-in';
    if (channel === 'phone') return 'Teléfono';
    return 'Mesa';
  };

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.content}
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
      <Text style={styles.greeting}>Hola {user?.name}</Text>
      <Text style={styles.subtitle}>Resumen operativo del día.</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}

      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <Text style={styles.heading}>Operación hoy</Text>
          {loading && <ActivityIndicator color="#fbbf24" />}
        </View>
        <View style={styles.cardRow}>
          <View>
            <Text style={styles.cardLabel}>Ventas del día</Text>
            <Text style={styles.cardValue}>
              {formatCurrency(opsTotals?.sales_total ?? 0)}
            </Text>
            <Text style={styles.cardMeta}>
              {formatDelta(opsTotals?.sales_delta_percent)}
            </Text>
          </View>
          <View>
            <Text style={styles.cardLabel}>Propinas</Text>
            <Text style={styles.cardValue}>
              {formatCurrency(opsTotals?.tips_total ?? 0)}
            </Text>
            <Text style={styles.cardMeta}>
              {opsTotals?.orders_count ?? 0} órdenes confirmadas
            </Text>
          </View>
        </View>
        <View style={styles.metrics}>
          <Metric label="Mesas abiertas" value={opsTotals?.open_tables ?? 0} />
          <Metric label="Tickets abiertos" value={opsTotals?.open_tickets ?? 0} />
          <Metric label="Meseros en turno" value={onlineServers.length} />
          <Metric
            label="Anulados"
            value={formatCurrency(opsTotals?.voided_total ?? 0)}
          />
        </View>
      </View>

      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <Text style={styles.heading}>Comparativo semanal</Text>
        </View>
        <View style={styles.metrics}>
          <Metric
            label="Ventas semana"
            value={formatCurrency(opsTotals?.sales_week_total ?? 0)}
          />
          <Metric
            label="Semana pasada"
            value={formatCurrency(opsTotals?.sales_week_prev ?? 0)}
          />
          <Metric
            label="Cambio"
            value={formatDelta(opsTotals?.sales_week_delta_percent)}
          />
        </View>
      </View>

      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <Text style={styles.heading}>Ventas por canal</Text>
        </View>
        {salesByChannel.length ? (
          <View style={styles.list}>
            {salesByChannel.map(item => (
              <View key={`channel-${item.channel}`} style={styles.listRow}>
                <View>
                  <Text style={styles.listTitle}>{formatChannel(item.channel)}</Text>
                  <Text style={styles.listMeta}>
                    {item.orders_count} órdenes
                  </Text>
                </View>
                <Text style={styles.listValue}>
                  {formatCurrency(item.sales_total)}
                </Text>
              </View>
            ))}
          </View>
        ) : (
          <Text style={styles.subtitle}>Sin datos por canal.</Text>
        )}
      </View>

      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <Text style={styles.heading}>Top productos del día</Text>
        </View>
        {topItems.length ? (
          <View style={styles.list}>
            {topItems.map(item => (
              <View key={`top-${item.name}`} style={styles.listRow}>
                <View>
                  <Text style={styles.listTitle}>{item.name}</Text>
                  <Text style={styles.listMeta}>
                    {item.quantity} vendidos
                  </Text>
                </View>
                <Text style={styles.listValue}>
                  {formatCurrency(item.revenue)}
                </Text>
              </View>
            ))}
          </View>
        ) : (
          <Text style={styles.subtitle}>Sin productos destacados.</Text>
        )}
      </View>

      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <Text style={styles.heading}>Equipo en turno</Text>
          <TouchableOpacity
            style={styles.refreshButton}
            onPress={() => loadData()}>
            <Text style={styles.refreshText}>Actualizar</Text>
          </TouchableOpacity>
        </View>
        {servers.length === 0 ? (
          <Text style={styles.subtitle}>Aún no hay meseros registrados.</Text>
        ) : (
          servers.map(server => (
            <View key={server.id} style={styles.serverItem}>
              <View style={styles.serverInfo}>
                <Text style={styles.serverName}>{server.name}</Text>
                <Text style={styles.serverEmail}>{server.email}</Text>
                <Text style={styles.serverStatus}>
                  {server.is_online ? 'En turno' : 'Fuera de turno'} ·{' '}
                  {server.active ? 'Activo' : 'Pausado'}
                </Text>
                <Text style={styles.serverMeta}>
                  Mesas: {server.active_tables} · Pendientes:{' '}
                  {server.open_orders}
                </Text>
                <Text style={styles.serverMeta}>
                  Ventas: {formatCurrency(server.sales_total)} · Propinas:{' '}
                  {formatCurrency(server.tips_total)}
                </Text>
                <Text style={styles.serverMeta}>
                  Última actividad: {formatLastSeen(server.last_seen_at)}
                </Text>
              </View>
              <TouchableOpacity
                style={[
                  styles.smallButton,
                  !server.active && styles.smallButtonOutline,
                ]}
                onPress={() => handleToggle(server.id)}
                disabled={togglingId === server.id}>
                {togglingId === server.id ? (
                  <ActivityIndicator
                    color={server.active ? '#0f172a' : '#fbbf24'}
                  />
                ) : (
                  <Text
                    style={[
                      styles.smallButtonText,
                      !server.active && styles.smallButtonOutlineText,
                    ]}>
                    {server.active ? 'Pausar' : 'Activar'}
                  </Text>
                )}
              </TouchableOpacity>
            </View>
          ))
        )}
      </View>

      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <Text style={styles.heading}>Fidelidad</Text>
        </View>
        <View style={styles.metrics}>
          <Metric label="Total" value={summary?.totals.total_visits ?? 0} />
          <Metric label="Pendientes" value={summary?.totals.pending_visits ?? 0} />
          <Metric
            label="Confirmadas"
            value={summary?.totals.confirmed_visits ?? 0}
          />
          <Metric
            label="Pts distribuidos"
            value={summary?.totals.points_distributed ?? 0}
          />
        </View>
      </View>

      <TouchableOpacity style={styles.logoutButton} onPress={logout}>
        <Text style={styles.logoutText}>Cerrar sesión</Text>
      </TouchableOpacity>
    </ScrollView>
  );
};

const Metric = ({label, value}: {label: string; value: number | string}) => (
  <View style={styles.metricCard}>
    <Text style={styles.metricValue}>{value}</Text>
    <Text style={styles.metricLabel}>{label}</Text>
  </View>
);

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
  greeting: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
  subtitle: {
    color: '#94a3b8',
    fontSize: 14,
  },
  error: {
    color: '#fb7185',
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 24,
    padding: 20,
    borderWidth: 1,
    borderColor: '#1e293b',
    gap: 12,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  cardRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 16,
  },
  heading: {
    fontSize: 18,
    fontWeight: '700',
    color: '#f8fafc',
  },
  metrics: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  metricCard: {
    flexBasis: '48%',
    backgroundColor: '#1e293b',
    borderRadius: 18,
    padding: 12,
  },
  metricValue: {
    fontSize: 20,
    fontWeight: '700',
    color: '#f8fafc',
  },
  metricLabel: {
    color: '#94a3b8',
    marginTop: 4,
  },
  cardMeta: {
    color: '#94a3b8',
    fontSize: 12,
    marginTop: 4,
  },
  list: {
    gap: 10,
  },
  listRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#1e293b',
  },
  listTitle: {
    color: '#f8fafc',
    fontWeight: '600',
  },
  listMeta: {
    color: '#94a3b8',
    fontSize: 12,
  },
  listValue: {
    color: '#fbbf24',
    fontWeight: '700',
  },
  serverItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#1e293b',
  },
  serverInfo: {
    flex: 1,
    paddingRight: 12,
  },
  serverName: {
    color: '#f8fafc',
    fontWeight: '600',
  },
  serverEmail: {
    color: '#94a3b8',
    fontSize: 12,
  },
  serverStatus: {
    color: '#cbd5f5',
    fontSize: 12,
  },
  serverMeta: {
    color: '#94a3b8',
    fontSize: 12,
  },
  smallButton: {
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingVertical: 8,
    paddingHorizontal: 16,
  },
  smallButtonText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  smallButtonOutline: {
    backgroundColor: 'transparent',
    borderWidth: 1,
    borderColor: '#fbbf24',
  },
  smallButtonOutlineText: {
    color: '#fbbf24',
  },
  refreshButton: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#fbbf24',
    paddingHorizontal: 14,
    paddingVertical: 6,
  },
  refreshText: {
    color: '#fbbf24',
    fontWeight: '600',
  },
  logoutButton: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    paddingVertical: 14,
    alignItems: 'center',
  },
  logoutText: {
    color: '#cbd5f5',
    fontWeight: '600',
  },
});

export default ManagerDashboardScreen;
