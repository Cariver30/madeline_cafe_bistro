import React from 'react';
import {Text, TouchableOpacity} from 'react-native';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import KitchenHomeScreen from '../screens/kitchen/KitchenHomeScreen';
import KitchenOrdersScreen from '../screens/kitchen/KitchenOrdersScreen';
import {KitchenStackParamList} from './kitchenTypes';
import {useAuth} from '../context/AuthContext';

const Stack = createNativeStackNavigator<KitchenStackParamList>();

const KitchenLogoutButton = () => {
  const {logout} = useAuth();
  return (
    <TouchableOpacity onPress={logout} style={{paddingHorizontal: 12}}>
      <Text style={{color: '#fbbf24', fontWeight: '700'}}>Salir</Text>
    </TouchableOpacity>
  );
};

const KitchenNavigator = () => (
  <Stack.Navigator
    screenOptions={{
      headerStyle: {backgroundColor: '#020617'},
      headerTintColor: '#f8fafc',
      contentStyle: {backgroundColor: '#020617'},
    }}>
    <Stack.Screen
      name="KitchenHome"
      component={KitchenHomeScreen}
      options={{
        title: 'Cocina',
        headerRight: () => <KitchenLogoutButton />,
      }}
    />
    <Stack.Screen
      name="KitchenOrders"
      component={KitchenOrdersScreen}
      options={({route}) => ({
        title: route.params?.title ?? 'Órdenes',
        headerRight: () => <KitchenLogoutButton />,
      })}
    />
  </Stack.Navigator>
);

export default KitchenNavigator;
