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
import {useFocusEffect, useNavigation} from '@react-navigation/native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {SafeAreaView} from 'react-native-safe-area-context';
import {useAuth} from '../../context/AuthContext';
import {getKitchenAreas} from '../../services/api';
import {KitchenStackParamList} from '../../navigation/kitchenTypes';
import {PrepArea} from '../../types';

const KitchenHomeScreen = () => {
  const {token, user} = useAuth();
  const navigation =
    useNavigation<NativeStackNavigationProp<KitchenStackParamList>>();
  const [areas, setAreas] = useState<PrepArea[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadAreas = useCallback(
    async (showLoader = true) => {
      if (!token) {
        return;
      }
      try {
        if (showLoader) {
          setLoading(true);
        }
        const data = await getKitchenAreas(token);
        setAreas(data);
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
      loadAreas();
    }, [loadAreas]),
  );

  const handleRefresh = () => {
    setRefreshing(true);
    loadAreas(false);
  };

  const openArea = (area: PrepArea) => {
    navigation.navigate('KitchenOrders', {
      areaId: area.id,
      title: area.name,
    });
  };

  const openLabel = (area: PrepArea, labelId: number, labelName: string) => {
    navigation.navigate('KitchenOrders', {
      areaId: area.id,
      labelId,
      title: `${area.name} · ${labelName}`,
    });
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={
          <RefreshControl
            tintColor="#fbbf24"
            refreshing={refreshing}
            onRefresh={handleRefresh}
          />
        }>
        <View style={styles.header}>
          <Text style={styles.greeting}>Hola {user?.name}</Text>
          <Text style={styles.subheading}>
            Selecciona un area o un label de preparacion.
          </Text>
          {error ? <Text style={styles.error}>{error}</Text> : null}
        </View>

        {loading && !areas.length ? (
          <View style={styles.loader}>
            <ActivityIndicator color="#fbbf24" />
          </View>
        ) : (
          areas.map(area => {
            const accent = area.color || '#fbbf24';
            const labels = area.labels ?? [];
            return (
              <View key={area.id} style={styles.areaCard}>
                <View style={styles.areaHeader}>
                  <View style={styles.areaTitleRow}>
                    <View style={[styles.areaDot, {backgroundColor: accent}]} />
                    <Text style={styles.areaTitle}>{area.name}</Text>
                  </View>
                  <TouchableOpacity
                    style={styles.areaAction}
                    onPress={() => openArea(area)}>
                    <Text style={styles.areaActionText}>Ver area</Text>
                  </TouchableOpacity>
                </View>
                <View style={styles.labelsRow}>
                  {labels.length ? (
                    labels.map(label => (
                      <TouchableOpacity
                        key={label.id}
                        style={[styles.labelChip, {borderColor: accent}]}
                        onPress={() =>
                          openLabel(area, label.id, label.name)
                        }>
                        <Text style={styles.labelText}>{label.name}</Text>
                      </TouchableOpacity>
                    ))
                  ) : (
                    <Text style={styles.emptyText}>
                      No hay labels activos.
                    </Text>
                  )}
                </View>
              </View>
            );
          })
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
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
  greeting: {
    fontSize: 16,
    color: '#f8fafc',
    fontWeight: '600',
  },
  subheading: {
    color: '#94a3b8',
    fontSize: 13,
  },
  error: {
    color: '#fb7185',
    fontSize: 13,
  },
  loader: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 24,
  },
  areaCard: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 12,
  },
  areaHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  areaTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  areaDot: {
    width: 10,
    height: 10,
    borderRadius: 999,
  },
  areaTitle: {
    color: '#f8fafc',
    fontSize: 16,
    fontWeight: '700',
  },
  areaAction: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#fbbf24',
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  areaActionText: {
    color: '#fbbf24',
    fontSize: 12,
    fontWeight: '700',
  },
  labelsRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  labelChip: {
    borderRadius: 999,
    borderWidth: 1,
    paddingHorizontal: 12,
    paddingVertical: 6,
    backgroundColor: '#0b1528',
  },
  labelText: {
    color: '#e2e8f0',
    fontSize: 12,
    fontWeight: '600',
  },
  emptyText: {
    color: '#64748b',
    fontSize: 12,
  },
});

export default KitchenHomeScreen;
