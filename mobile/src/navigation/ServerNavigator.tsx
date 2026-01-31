import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import {ServerSessionsProvider} from '../context/ServerSessionsContext';
import ServerHomeScreen from '../screens/server/ServerHomeScreen';
import TableDetailScreen from '../screens/server/TableDetailScreen';
import OrderDetailScreen from '../screens/server/OrderDetailScreen';
import NewTableScreen from '../screens/server/NewTableScreen';
import TakeOrderScreen from '../screens/server/TakeOrderScreen';
import ServerPaymentScreen from '../screens/server/ServerPaymentScreen';
import {ServerStackParamList} from './serverTypes';

const Stack = createNativeStackNavigator<ServerStackParamList>();

const ServerNavigator = () => (
  <ServerSessionsProvider>
    <Stack.Navigator
      screenOptions={{
        headerStyle: {backgroundColor: '#020617'},
        headerTintColor: '#f8fafc',
        contentStyle: {backgroundColor: '#020617'},
      }}>
      <Stack.Screen
        name="ServerHome"
        component={ServerHomeScreen}
        options={{headerShown: false}}
      />
      <Stack.Screen
        name="TableDetail"
        component={TableDetailScreen}
        options={{title: 'Mesa'}}
      />
      <Stack.Screen
        name="OrderDetail"
        component={OrderDetailScreen}
        options={{title: 'Orden'}}
      />
      <Stack.Screen
        name="NewTable"
        component={NewTableScreen}
        options={{title: 'Nueva mesa'}}
      />
      <Stack.Screen
        name="TakeOrder"
        component={TakeOrderScreen}
        options={{title: 'Tomar orden'}}
      />
      <Stack.Screen
        name="ServerPayment"
        component={ServerPaymentScreen}
        options={{title: 'Cobrar'}}
      />
    </Stack.Navigator>
  </ServerSessionsProvider>
);

export default ServerNavigator;
