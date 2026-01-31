import React from 'react';
import {StyleSheet, Text, TouchableOpacity, View} from 'react-native';

type Props = {
  title: string;
  priceLabel: string;
  categoryLabel?: string;
  quantity: number;
  hasExtras: boolean;
  selectedExtrasCount: number;
  expanded: boolean;
  isWide: boolean;
  onAdd: () => void;
  onRemove: () => void;
  onToggleOptions: () => void;
  children?: React.ReactNode;
};

export const PosItemCard = ({
  title,
  priceLabel,
  categoryLabel,
  quantity,
  hasExtras,
  selectedExtrasCount,
  expanded,
  isWide,
  onAdd,
  onRemove,
  onToggleOptions,
  children,
}: Props) => {
  return (
    <View style={[styles.card, isWide && styles.cardWide]}>
      <TouchableOpacity style={styles.info} activeOpacity={0.9} onPress={onAdd}>
        {categoryLabel ? (
          <Text style={styles.category}>{categoryLabel}</Text>
        ) : null}

        <View style={styles.titleRow}>
          <Text style={styles.name} numberOfLines={2}>
            {title}
          </Text>
          <Text style={styles.price}>{priceLabel}</Text>
        </View>

        {hasExtras ? (
          <View style={styles.hintRow}>
            <Text style={styles.hint}>
              Opciones{selectedExtrasCount ? ` (${selectedExtrasCount})` : ''}
            </Text>
            <TouchableOpacity
              style={[styles.optionsBtn, expanded && styles.optionsBtnActive]}
              onPress={onToggleOptions}>
              <Text
                style={[
                  styles.optionsBtnText,
                  expanded && styles.optionsBtnTextActive,
                ]}>
                {expanded ? 'Cerrar' : 'Opciones'}
              </Text>
            </TouchableOpacity>
          </View>
        ) : null}

        <Text style={styles.tapHint}>Toca para agregar</Text>
      </TouchableOpacity>

      <View style={styles.actions}>
        <TouchableOpacity style={styles.qtyBtn} onPress={onRemove}>
          <Text style={styles.qtyText}>-</Text>
        </TouchableOpacity>
        <Text style={styles.qtyValue}>{quantity}</Text>
        <TouchableOpacity style={styles.qtyBtn} onPress={onAdd}>
          <Text style={styles.qtyText}>+</Text>
        </TouchableOpacity>
      </View>

      {children}
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 18,
    padding: 14,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 10,
  },
  cardWide: {
    padding: 16,
  },
  info: {
    gap: 6,
  },
  category: {
    color: '#94a3b8',
    fontSize: 11,
    fontWeight: '600',
  },
  titleRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 10,
    alignItems: 'flex-start',
  },
  name: {
    color: '#f8fafc',
    fontSize: 15,
    fontWeight: '800',
    flex: 1,
  },
  price: {
    color: '#fbbf24',
    fontSize: 13,
    fontWeight: '800',
  },
  hintRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 10,
  },
  hint: {
    color: '#94a3b8',
    fontSize: 12,
    fontWeight: '600',
  },
  optionsBtn: {
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
  },
  optionsBtnActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  optionsBtnText: {
    color: '#e2e8f0',
    fontSize: 12,
    fontWeight: '700',
  },
  optionsBtnTextActive: {
    color: '#0f172a',
  },
  tapHint: {
    color: '#64748b',
    fontSize: 11,
  },
  actions: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    gap: 10,
  },
  qtyBtn: {
    width: 40,
    height: 36,
    borderRadius: 12,
    backgroundColor: '#1e293b',
    borderWidth: 1,
    borderColor: '#334155',
    alignItems: 'center',
    justifyContent: 'center',
  },
  qtyText: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '900',
  },
  qtyValue: {
    minWidth: 22,
    textAlign: 'center',
    color: '#f8fafc',
    fontSize: 14,
    fontWeight: '800',
  },
});
