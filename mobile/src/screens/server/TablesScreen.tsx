import React from 'react';
import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useNavigation} from '@react-navigation/native';
import {NativeStackNavigationProp} from '@react-navigation/native-stack';
import {TableCard} from '../../components/server/TableCard';
import {TablesHeader} from '../../components/server/TablesHeader';
import {useTablesViewModel} from '../../hooks/server/useTablesViewModel';
import {ServerStackParamList} from '../../navigation/serverTypes';

const TablesScreen = () => {
  const navigation =
    useNavigation<NativeStackNavigationProp<ServerStackParamList>>();
  const {tables, loading, refreshing, error, refresh} = useTablesViewModel();

  return (
    <View style={styles.container}>
      {loading && tables.length === 0 ? (
        <View style={styles.loader}>
          <ActivityIndicator color="#fbbf24" />
        </View>
      ) : (
        <FlatList
          data={tables}
          keyExtractor={item => `table-${item.id}`}
          refreshControl={
            <RefreshControl
              tintColor="#fbbf24"
              refreshing={refreshing}
              onRefresh={refresh}
            />
          }
          ListHeaderComponent={
            <TablesHeader
              onNewTable={() => navigation.navigate('NewTable')}
              error={error}
            />
          }
          ListEmptyComponent={
            <Text style={styles.emptyText}>Aún no hay mesas activas.</Text>
          }
          renderItem={({item}) => {
            const pendingOrder = item.orders?.find(
              order => order.status === 'pending',
            );
            return (
              <TableCard
                table={item}
                onPress={() =>
                  navigation.navigate('TableDetail', {sessionId: item.id})
                }
                onQuickOrder={() =>
                  navigation.navigate('TakeOrder', {sessionId: item.id})
                }
                onViewPending={() => {
                  if (pendingOrder) {
                    navigation.navigate('OrderDetail', {
                      orderId: pendingOrder.id,
                      sessionId: item.id,
                    });
                  } else {
                    navigation.navigate('TableDetail', {
                      sessionId: item.id,
                    });
                  }
                }}
              />
            );
          }}
          contentContainerStyle={styles.listContent}
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#020617',
  },
  listContent: {
    padding: 20,
    gap: 12,
    paddingBottom: 40,
  },
  loader: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyText: {
    color: '#94a3b8',
    textAlign: 'center',
    marginTop: 16,
  },
});

export default TablesScreen;
