import React from 'react';
import {StyleSheet, Text, TouchableOpacity, View} from 'react-native';

type TablesHeaderProps = {
  title?: string;
  onNewTable: () => void;
  error?: string | null;
};

export const TablesHeader = ({
  title = 'Mesas en curso',
  onNewTable,
  error,
}: TablesHeaderProps) => {
  return (
    <View style={styles.header}>
      <View style={styles.headerRow}>
        <Text style={styles.heading}>{title}</Text>
        <TouchableOpacity style={styles.newButton} onPress={onNewTable}>
          <Text style={styles.newButtonText}>Nueva mesa</Text>
        </TouchableOpacity>
      </View>
      {error ? <Text style={styles.error}>{error}</Text> : null}
    </View>
  );
};

const styles = StyleSheet.create({
  header: {
    gap: 6,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  heading: {
    fontSize: 18,
    fontWeight: '700',
    color: '#f8fafc',
  },
  newButton: {
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  newButtonText: {
    color: '#0f172a',
    fontWeight: '700',
    fontSize: 12,
  },
  error: {
    color: '#fb7185',
    fontSize: 14,
  },
});
