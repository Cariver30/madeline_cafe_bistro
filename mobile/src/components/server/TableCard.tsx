import React from 'react';
import {StyleSheet, Text, TouchableOpacity, View} from 'react-native';
import {TableSession} from '../../types';
import {TableViewModel} from '../../hooks/server/useTablesViewModel';

type TableCardProps = {
  table: TableSession & TableViewModel;
  onPress: () => void;
  onQuickOrder?: () => void;
  onViewPending?: () => void;
};

export const TableCard = ({
  table,
  onPress,
  onQuickOrder,
  onViewPending,
}: TableCardProps) => {
  const priorityStyles = {
    urgent: styles.urgent,
    warning: styles.warning,
    normal: styles.normal,
  } as const;

  return (
    <TouchableOpacity
      style={[styles.card, priorityStyles[table.priority]]}
      onPress={onPress}>
      <View style={styles.row}>
        <Text style={styles.title}>Mesa {table.table_label}</Text>

        {table.pendingCount > 0 ? (
          <View style={styles.badge}>
            <Text style={styles.badgeText}>{table.pendingCount}</Text>
          </View>
        ) : null}
      </View>

      <Text style={styles.meta}>
        {table.guest_name} · {table.party_size} personas
      </Text>

      <Text style={styles.meta}>
        {table.priority === 'urgent'
          ? 'Órdenes pendientes'
          : table.priority === 'warning'
          ? `Expira en ${table.minutesLeft} min`
          : table.timeclock?.remaining_minutes != null
          ? `Restan ${table.timeclock.remaining_minutes} min`
          : 'Activa'}
      </Text>

      <View style={styles.actions}>
        <TouchableOpacity
          style={[styles.actionButton, styles.primaryButton]}
          onPress={onQuickOrder}>
          <Text style={styles.actionText}>Tomar orden</Text>
        </TouchableOpacity>

        {table.pendingCount > 0 ? (
          <TouchableOpacity
            style={[styles.actionButton, styles.secondaryButton]}
            onPress={onViewPending}>
            <Text style={styles.actionText}>Ver pendientes</Text>
          </TouchableOpacity>
        ) : null}
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 8,
  },
  urgent: {
    borderColor: '#fbbf24',
    backgroundColor: '#0b1220',
  },
  warning: {
    borderColor: '#fb7185',
  },
  normal: {},
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  title: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 16,
  },
  meta: {
    color: '#94a3b8',
    fontSize: 13,
  },
  badge: {
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  badgeText: {
    color: '#0f172a',
    fontSize: 11,
    fontWeight: '700',
  },
  actions: {
    flexDirection: 'row',
    gap: 8,
  },
  actionButton: {
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  primaryButton: {
    backgroundColor: '#22c55e',
  },
  secondaryButton: {
    backgroundColor: '#38bdf8',
  },
  actionText: {
    color: '#0f172a',
    fontWeight: '700',
    fontSize: 12,
  },
});
