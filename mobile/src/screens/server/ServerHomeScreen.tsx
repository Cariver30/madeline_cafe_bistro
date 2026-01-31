import React from 'react';
import {TouchableOpacity, Text} from 'react-native';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import ServerDashboardScreen from './ServerDashboardScreen';
import ServerLoyaltyScreen from './ServerLoyaltyScreen';
import PendingOrdersScreen from './PendingOrdersScreen';
import TablesScreen from './TablesScreen';
import {ServerTabParamList} from '../../navigation/serverTypes';
import {useServerSessions} from '../../context/ServerSessionsContext';
import {useAuth} from '../../context/AuthContext';
import TabIcon from '../../components/TabIcon';

const Tab = createBottomTabNavigator<ServerTabParamList>();

const ServerHomeScreen = () => {
  const {pendingOrdersTotal} = useServerSessions();
  const {logout} = useAuth();

  return (
    <Tab.Navigator
      initialRouteName="Dashboard"
      screenOptions={{
        headerStyle: {backgroundColor: '#020617'},
        headerTintColor: '#f8fafc',
        headerRightContainerStyle: {paddingRight: 12},
        headerRight: () => (
          <TouchableOpacity onPress={logout}>
            <Text style={{color: '#f8fafc', fontWeight: '600', fontSize: 12}}>
              Cerrar sesión
            </Text>
          </TouchableOpacity>
        ),
        tabBarActiveTintColor: '#0f172a',
        tabBarInactiveTintColor: '#e2e8f0',
        tabBarStyle: {backgroundColor: '#0f172a'},
        tabBarActiveBackgroundColor: '#fbbf24',
        tabBarInactiveBackgroundColor: '#0f172a',
        tabBarShowLabel: false,
      }}>
      <Tab.Screen
        name="Dashboard"
        component={ServerDashboardScreen}
        options={{
          title: 'Resumen',
          tabBarIcon: ({color, size}) => (
            <TabIcon name="dashboard" color={color} size={size} />
          ),
        }}
      />
      <Tab.Screen
        name="Loyalty"
        component={ServerLoyaltyScreen}
        options={{
          title: 'Fidelidad',
          tabBarIcon: ({color, size}) => (
            <TabIcon name="loyalty" color={color} size={size} />
          ),
        }}
      />
      <Tab.Screen
        name="PendingOrders"
        component={PendingOrdersScreen}
        options={{
          title: 'Órdenes pendientes',
          tabBarBadge: pendingOrdersTotal > 0 ? pendingOrdersTotal : undefined,
          tabBarIcon: ({color, size}) => (
            <TabIcon name="orders" color={color} size={size} />
          ),
        }}
      />
      <Tab.Screen
        name="Tables"
        component={TablesScreen}
        options={{
          title: 'Mesas',
          tabBarIcon: ({color, size}) => (
            <TabIcon name="tables" color={color} size={size} />
          ),
        }}
      />
    </Tab.Navigator>
  );
};

export default ServerHomeScreen;
