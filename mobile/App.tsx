import React from 'react';
import {StatusBar, View} from 'react-native';
import {SafeAreaProvider} from 'react-native-safe-area-context';
import {AuthProvider} from './src/context/AuthContext';
import AppNavigation from './src/navigation/RootNavigator';

function App(): React.JSX.Element {
  return (
    <SafeAreaProvider>
      <StatusBar barStyle="light-content" />
      <View style={{flex: 1, backgroundColor: '#020617'}}>
        <AuthProvider>
          <AppNavigation />
        </AuthProvider>
      </View>
    </SafeAreaProvider>
  );
}

export default App;
