import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import {PosTicketsProvider} from '../context/PosTicketsContext';
import {PosStackParamList} from './posTypes';
import PosHomeScreen from '../screens/pos/PosHomeScreen';
import PosTicketScreen from '../screens/pos/PosTicketScreen';
import PosNewTicketScreen from '../screens/pos/PosNewTicketScreen';
import PosTakeOrderScreen from '../screens/pos/PosTakeOrderScreen';
import PosPaymentScreen from '../screens/pos/PosPaymentScreen';

const Stack = createNativeStackNavigator<PosStackParamList>();

const PosNavigator = () => (
  <PosTicketsProvider>
    <Stack.Navigator
      screenOptions={{
        headerShown: false,
        contentStyle: {backgroundColor: '#020617'},
      }}>
      <Stack.Screen name="PosHome" component={PosHomeScreen} />
      <Stack.Screen name="PosTicket" component={PosTicketScreen} />
      <Stack.Screen name="PosNewTicket" component={PosNewTicketScreen} />
      <Stack.Screen name="PosTakeOrder" component={PosTakeOrderScreen} />
      <Stack.Screen name="PosPayment" component={PosPaymentScreen} />
    </Stack.Navigator>
  </PosTicketsProvider>
);

export default PosNavigator;
