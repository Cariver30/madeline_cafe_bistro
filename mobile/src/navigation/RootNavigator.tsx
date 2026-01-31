import React from 'react';
import {ActivityIndicator, View} from 'react-native';
import {NavigationContainer} from '@react-navigation/native';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import LoginScreen from '../screens/LoginScreen';
import ServerNavigator from './ServerNavigator';
import PosNavigator from './PosNavigator';
import KitchenNavigator from './KitchenNavigator';
import ManagerDashboardScreen from '../screens/manager/ManagerDashboardScreen';
import ManagerMenuScreen from '../screens/manager/ManagerMenuScreen';
import ManagerCampaignsScreen from '../screens/manager/ManagerCampaignsScreen';
import {useAuth} from '../context/AuthContext';
import TabIcon from '../components/TabIcon';

export type RootStackParamList = {
  Login: undefined;
  Server: undefined;
  Manager: undefined;
  Pos: undefined;
  Kitchen: undefined;
};

const Stack = createNativeStackNavigator<RootStackParamList>();
const Tab = createBottomTabNavigator();

const ManagerTabs = () => (
  <Tab.Navigator
    screenOptions={{
      headerStyle: {backgroundColor: '#020617'},
      headerTintColor: '#f8fafc',
      tabBarActiveTintColor: '#0f172a',
      tabBarInactiveTintColor: '#e2e8f0',
      tabBarStyle: {backgroundColor: '#0f172a'},
      tabBarActiveBackgroundColor: '#fbbf24',
      tabBarInactiveBackgroundColor: '#0f172a',
      tabBarShowLabel: false,
    }}>
    <Tab.Screen
      name="Dashboard"
      component={ManagerDashboardScreen}
      options={{
        title: 'Panel',
        tabBarIcon: ({color, size}) => (
          <TabIcon name="dashboard" color={color} size={size} />
        ),
      }}
    />
    <Tab.Screen
      name="Menu"
      component={ManagerMenuScreen}
      options={{
        title: 'Menú / Vistas',
        tabBarIcon: ({color, size}) => (
          <TabIcon name="menu" color={color} size={size} />
        ),
      }}
    />
    <Tab.Screen
      name="Servers"
      component={ServerNavigator}
      options={{
        title: 'Meseros',
        headerShown: false,
        tabBarIcon: ({color, size}) => (
          <TabIcon name="tables" color={color} size={size} />
        ),
      }}
    />
    <Tab.Screen
      name="Pos"
      component={PosNavigator}
      options={{
        title: 'POS',
        headerShown: false,
        tabBarIcon: ({color, size}) => (
          <TabIcon name="pos" color={color} size={size} />
        ),
      }}
    />
    <Tab.Screen
      name="Campaigns"
      component={ManagerCampaignsScreen}
      options={{
        title: 'Campañas',
        tabBarIcon: ({color, size}) => (
          <TabIcon name="campaigns" color={color} size={size} />
        ),
      }}
    />
  </Tab.Navigator>
);

const SplashScreen = () => (
  <View
    style={{
      flex: 1,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: '#020617',
    }}>
    <ActivityIndicator size="large" color="#fbbf24" />
  </View>
);

const RootStack = () => {
  const {user, initializing} = useAuth();

  if (initializing) {
    return <SplashScreen />;
  }

  return (
    <Stack.Navigator
      screenOptions={{
        headerShown: false,
        contentStyle: {backgroundColor: '#020617'},
      }}>
      {!user ? (
        <Stack.Screen name="Login" component={LoginScreen} />
      ) : user.role === 'manager' ? (
        <Stack.Screen name="Manager" component={ManagerTabs} />
      ) : user.role === 'pos' ? (
        <Stack.Screen name="Pos" component={PosNavigator} />
      ) : user.role === 'kitchen' ? (
        <Stack.Screen name="Kitchen" component={KitchenNavigator} />
      ) : (
        <Stack.Screen name="Server" component={ServerNavigator} />
      )}
    </Stack.Navigator>
  );
};

const AppNavigation = () => (
  <NavigationContainer>
    <RootStack />
  </NavigationContainer>
);

export default AppNavigation;
