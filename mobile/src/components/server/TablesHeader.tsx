import React from 'react';
import {StyleSheet, Text, TouchableOpacity, View} from 'react-native';

type TablesHeaderProps = {
  title?: string;
  onNewTable: () => void;
  error?: string | null;
  attentionCount?: number;
};

export const TablesHeader = ({
  title = 'Mesas en curso',
  onNewTable,
  error,
  attentionCount = 0,
}: TablesHeaderProps) => {
  return (
    <View style={styles.header}>
      <View style={styles.headerRow}>
        <Text style={styles.heading}>{title}</Text>
        <TouchableOpacity style={styles.newButton} onPress={onNewTable}>
          <Text style={styles.newButtonText}>Nueva mesa</Text>
        </TouchableOpacity>
      </View>
      {attentionCount > 0 ? (
        <View style={styles.noticeBanner}>
          <Text style={styles.noticeText}>
            Órdenes sin atender: {attentionCount}
          </Text>
        </View>
      ) : null}
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
  noticeBanner: {
    backgroundColor: '#fbbf24',
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 12,
  },
  noticeText: {
    color: '#0f172a',
    fontWeight: '700',
    fontSize: 13,
  },
  error: {
    color: '#fb7185',
    fontSize: 14,
  },
});
