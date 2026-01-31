import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';
import {useAuth} from '../../context/AuthContext';
import {getCampaigns} from '../../services/api';
import {Campaign} from '../../types';

const ManagerCampaignsScreen = () => {
  const {token} = useAuth();
  const [campaigns, setCampaigns] = useState<Campaign[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadData = useCallback(
    async (showLoader = true) => {
      if (!token) {
        return;
      }
      try {
        if (showLoader) {
          setLoading(true);
        }
        const data = await getCampaigns(token);
        setCampaigns(data);
        setError(null);
      } catch (err) {
        setError(
          err instanceof Error
            ? err.message
            : 'No se pudieron cargar las campañas.',
        );
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
      <View style={styles.card}>
        <View style={styles.header}>
          <Text style={styles.heading}>Campañas activas</Text>
          {loading && <ActivityIndicator color="#fbbf24" />}
        </View>
        {error ? <Text style={styles.error}>{error}</Text> : null}
        {campaigns.length === 0 ? (
          <Text style={styles.subtitle}>
            Todavía no tienes campañas configuradas.
          </Text>
        ) : (
          campaigns.map(campaign => (
            <View key={campaign.id} style={styles.campaign}>
              <View style={styles.row}>
                <Text style={styles.title}>{campaign.title}</Text>
                <Text style={styles.badge}>
                  {campaign.active ? 'Activo' : 'Inactivo'}
                </Text>
              </View>
              <Text style={styles.subtitle}>
                Vista: {campaign.view.toUpperCase()}
              </Text>
              <Text style={styles.dates}>
                {campaign.start_date} → {campaign.end_date}
              </Text>
              <Text style={styles.subtitle}>
                Repite en días:{' '}
                {campaign.repeat_days.length
                  ? campaign.repeat_days.join(', ')
                  : 'Todos'}
              </Text>
            </View>
          ))
        )}
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
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 24,
    padding: 20,
    borderWidth: 1,
    borderColor: '#1e293b',
    gap: 14,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  heading: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
  error: {
    color: '#fb7185',
  },
  subtitle: {
    color: '#94a3b8',
  },
  campaign: {
    borderTopWidth: 1,
    borderTopColor: '#1e293b',
    paddingTop: 12,
    gap: 4,
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  title: {
    color: '#f8fafc',
    fontWeight: '600',
    fontSize: 16,
  },
  badge: {
    color: '#fbbf24',
    fontWeight: '700',
  },
  dates: {
    color: '#cbd5f5',
    fontSize: 12,
  },
});

export default ManagerCampaignsScreen;
