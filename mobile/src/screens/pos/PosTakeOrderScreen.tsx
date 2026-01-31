import React, {useCallback, useEffect, useMemo, useRef, useState} from 'react';
import {
  ActivityIndicator,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
  useWindowDimensions,
} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {useAuth} from '../../context/AuthContext';
import {usePosTickets} from '../../context/PosTicketsContext';
import {PosStackParamList} from '../../navigation/posTypes';
import {PosItemCard} from '../../components/pos/PosItemCard';
import {getPosMenuCategories} from '../../services/api';
import {
  CategoryPayload,
  ManagerView,
  ServerOrderItemPayload,
  ExtraOption,
  UpsellItem,
} from '../../types';

const VIEW_LABELS: Record<ManagerView, string> = {
  menu: 'Menu',
  cocktails: 'Cocteles',
  wines: 'Bebidas',
};

const VIEW_TO_TYPE: Record<ManagerView, ServerOrderItemPayload['type']> = {
  menu: 'dish',
  cocktails: 'cocktail',
  wines: 'wine',
};

const TYPE_TO_VIEW: Record<ServerOrderItemPayload['type'], ManagerView> = {
  dish: 'menu',
  cocktail: 'cocktails',
  wine: 'wines',
};

const UPSELL_TYPE_LABEL: Record<ServerOrderItemPayload['type'], string> = {
  dish: 'Plato',
  cocktail: 'Cóctel',
  wine: 'Bebida',
};

type CartItem = {
  key: string;
  id: number;
  name: string;
  price: number;
  quantity: number;
  type: ServerOrderItemPayload['type'];
  extras: number[];
};

const PosTakeOrderScreen = ({route, navigation}: NativeStackScreenProps<PosStackParamList, 'PosTakeOrder'>) => {
  const {ticketId} = route.params;
  const {token} = useAuth();
  const {getTicketById, addItems} = usePosTickets();
  const ticket = getTicketById(ticketId);
  const {width} = useWindowDimensions();
  const isWide = width >= 900;
  const [activeView, setActiveView] = useState<ManagerView>('menu');
  const [categoriesByView, setCategoriesByView] = useState<Record<ManagerView, CategoryPayload[]>>({
    menu: [],
    cocktails: [],
    wines: [],
  });
  const [activeCategoryByView, setActiveCategoryByView] = useState<Record<ManagerView, number | null>>({
    menu: null,
    cocktails: null,
    wines: null,
  });
  const [loadingView, setLoadingView] = useState<Record<ManagerView, boolean>>({
    menu: false,
    cocktails: false,
    wines: false,
  });
  const [errorsByView, setErrorsByView] = useState<Record<ManagerView, string | null>>({
    menu: null,
    cocktails: null,
    wines: null,
  });
  const [searchTerm, setSearchTerm] = useState('');
  const [cart, setCart] = useState<Record<string, CartItem>>({});
  const [expandedItemKey, setExpandedItemKey] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const loadedViews = useRef<Record<ManagerView, boolean>>({
    menu: false,
    cocktails: false,
    wines: false,
  });
  const isMounted = useRef(true);

  const findItemByType = useCallback(
    (type: ServerOrderItemPayload['type'], id: number) => {
      const view = TYPE_TO_VIEW[type];
      const categories = categoriesByView[view] ?? [];
      for (const category of categories) {
        const item = (category.dishes ?? []).find(candidate => candidate.id === id);
        if (item) {
          return {item, categoryId: category.id};
        }
      }
      return {item: undefined, categoryId: null};
    },
    [categoriesByView],
  );

  useEffect(() => {
    return () => {
      isMounted.current = false;
    };
  }, []);

  const loadView = useCallback(
    async (view: ManagerView, force = false) => {
      if (!token || (!force && loadedViews.current[view])) {
        return;
      }
      setLoadingView(prev => ({...prev, [view]: true}));
      try {
        const data = await getPosMenuCategories(token, view);
        if (!isMounted.current) return;
        setCategoriesByView(prev => ({...prev, [view]: data}));
        loadedViews.current[view] = true;
        setErrorsByView(prev => ({...prev, [view]: null}));
    } catch (err) {
      if (!isMounted.current) return;
      const message =
        err instanceof Error ? err.message : 'No se pudo cargar.';
      setErrorsByView(prev => ({
        ...prev,
        [view]: message,
      }));
      } finally {
        if (isMounted.current) {
          setLoadingView(prev => ({...prev, [view]: false}));
        }
      }
    },
    [token],
  );

  useEffect(() => {
    if (!token) {
      return;
    }
    loadedViews.current = {menu: false, cocktails: false, wines: false};
    setCategoriesByView({menu: [], cocktails: [], wines: []});
    setErrorsByView({menu: null, cocktails: null, wines: null});
    setActiveCategoryByView({menu: null, cocktails: null, wines: null});

    (['menu', 'cocktails', 'wines'] as ManagerView[]).forEach(view => {
      void loadView(view, true);
    });
  }, [token, loadView]);

  const categories = categoriesByView[activeView];
  const activeCategoryId = activeCategoryByView[activeView];

  useEffect(() => {
    if (!categories.length) {
      return;
    }
    setActiveCategoryByView(prev => {
      const currentId = prev[activeView];
      if (currentId && categories.some(category => category.id === currentId)) {
        return prev;
      }
      return {...prev, [activeView]: categories[0].id};
    });
  }, [categories, activeView]);

  const itemsToShow = useMemo(() => {
    const term = searchTerm.trim().toLowerCase();
    const entries = categories.flatMap(category =>
      (category.dishes ?? []).map(item => ({
        item,
        categoryName: category.name,
      })),
    );
    if (term) {
      return entries.filter(entry =>
        entry.item.name.toLowerCase().includes(term),
      );
    }
    const activeCategory =
      categories.find(category => category.id === activeCategoryId) ??
      categories[0];
    return (activeCategory?.dishes ?? []).map(item => ({
      item,
      categoryName: activeCategory?.name ?? '',
    }));
  }, [categories, activeCategoryId, searchTerm]);

  const groupExtras = (extras: ExtraOption[]) => {
    return extras.reduce<
      Record<
        string,
        {
          kind: string;
          required: boolean;
          maxSelect: number | null;
          minSelect: number | null;
          options: ExtraOption[];
        }
      >
    >((groups, extra) => {
      if (extra.active === false) {
        return groups;
      }
      const group = extra.group_name || extra.name || 'Opciones';
      if (!groups[group]) {
        groups[group] = {
          kind: extra.kind ?? 'modifier',
          required: !!extra.group_required || !!extra.min_select,
          maxSelect: extra.max_select ?? null,
          minSelect: extra.min_select ?? null,
          options: [],
        };
      }
      if (!groups[group].kind && extra.kind) {
        groups[group].kind = extra.kind;
      }
      if (extra.group_required || extra.min_select) {
        groups[group].required = true;
      }
      if (extra.max_select) {
        groups[group].maxSelect = extra.max_select;
      }
      if (extra.min_select) {
        groups[group].minSelect = extra.min_select;
      }
      groups[group].options.push(extra);
      return groups;
    }, {});
  };

  const hasRequiredExtras = (extras: ExtraOption[]) => {
    const groups = groupExtras(extras ?? []);
    return Object.values(groups).some(
      group => group.required || (group.minSelect ?? 0) > 0,
    );
  };

  const handleUpsellPress = (upsell: UpsellItem) => {
    const view = TYPE_TO_VIEW[upsell.type];
    const {item: target, categoryId} = findItemByType(upsell.type, upsell.id);
    const extrasByGroup = groupExtras(target?.extras ?? []);

    if (hasRequiredExtras(target?.extras ?? [])) {
      setActiveView(view);
      setSearchTerm('');
      if (categoryId) {
        setActiveCategoryByView(prev => ({
          ...prev,
          [view]: categoryId,
        }));
      }
      setExpandedItemKey(`${view}-${upsell.id}`);
      return;
    }

    const key = `${view}-${upsell.id}`;
    const cartItem: CartItem = {
      key,
      id: upsell.id,
      name: target?.name ?? upsell.name,
      price: Number(target?.price ?? upsell.price ?? 0),
      quantity: cart[key]?.quantity ?? 0,
      type: upsell.type,
      extras: cart[key]?.extras ?? [],
    };

    addItem(cartItem, extrasByGroup);
  };

  const addItem = (
    item: CartItem,
    extrasByGroup: Record<
      string,
      {kind: string; required: boolean; maxSelect: number | null; options: ExtraOption[]}
    >,
  ) => {
    const missingRequired = Object.entries(extrasByGroup)
      .filter(([_, group]) => group.required || (group.minSelect ?? 0) > 0)
      .filter(([_, group]) => {
        const optionIds = group.options.map(option => option.id);
        const selectedCount = item.extras.filter(id =>
          optionIds.includes(id),
        ).length;
        const requiredMin = Math.max(group.required ? 1 : 0, group.minSelect ?? 0);
        return selectedCount < requiredMin;
      })
      .map(([groupName]) => groupName);

    if (missingRequired.length) {
      setExpandedItemKey(item.key);
      setErrorsByView(prev => ({
        ...prev,
        [activeView]: `Selecciona las opciones requeridas: ${missingRequired.join(', ')}`,
      }));
      return;
    }

    setCart(prev => {
      const existing = prev[item.key];
      const nextQty = (existing?.quantity ?? 0) + 1;
      return {
        ...prev,
        [item.key]: {
          ...item,
          quantity: nextQty,
          extras: existing?.extras ?? item.extras ?? [],
        },
      };
    });
  };

  const removeItem = (key: string) => {
    setCart(prev => {
      const existing = prev[key];
      if (!existing) {
        return prev;
      }
      const nextQty = existing.quantity - 1;
      if (nextQty <= 0) {
        const {...rest} = prev;
        delete rest[key];
        return rest;
      }
      return {
        ...prev,
        [key]: {...existing, quantity: nextQty},
      };
    });
  };

  const incrementItem = (key: string) => {
    setCart(prev => {
      const existing = prev[key];
      if (!existing) {
        return prev;
      }
      return {
        ...prev,
        [key]: {...existing, quantity: existing.quantity + 1},
      };
    });
  };

  const toggleExtra = (
    item: CartItem,
    extra: ExtraOption,
    groupOptionIds: number[],
    maxSelect: number | null,
  ) => {
    setCart(prev => {
      const existing = prev[item.key] ?? {...item, quantity: 1, extras: []};
      let selected = existing.extras ?? [];

      if (maxSelect === 1) {
        selected = selected.filter(id => !groupOptionIds.includes(id));
        selected = [...selected, extra.id];
      } else {
        if (selected.includes(extra.id)) {
          selected = selected.filter(id => id !== extra.id);
        } else {
          const selectedInGroup = selected.filter(id =>
            groupOptionIds.includes(id),
          );
          if (!maxSelect || selectedInGroup.length < maxSelect) {
            selected = [...selected, extra.id];
          }
        }
      }

      return {
        ...prev,
        [item.key]: {...existing, extras: selected},
      };
    });
  };

  const totals = useMemo(() => {
    const items = Object.values(cart);
    const count = items.reduce((sum, item) => sum + item.quantity, 0);
    const total = items.reduce(
      (sum, item) => sum + item.quantity * item.price,
      0,
    );
    return {count, total};
  }, [cart]);

  const extrasLookup = useMemo(() => {
    const lookup = new Map<number, ExtraOption>();
    (Object.values(categoriesByView) as CategoryPayload[][]).forEach(view => {
      view.forEach(category => {
        (category.dishes ?? []).forEach(item => {
          (item.extras ?? []).forEach(extra => {
            lookup.set(extra.id, extra);
          });
        });
      });
    });
    return lookup;
  }, [categoriesByView]);

  const cartItems = useMemo(() => Object.values(cart), [cart]);
  const hasSearch = Boolean(searchTerm.trim());
  const headerTitle = `Ticket #${ticket?.ticket_id ?? '—'} · Agregar items`;

  const handleSubmit = async () => {
    if (!token || totals.count === 0) {
      return;
    }
    setSubmitting(true);
    setErrorsByView(prev => ({...prev, [activeView]: null}));
    try {
      const items: ServerOrderItemPayload[] = Object.values(cart).map(item => ({
        type: item.type,
        id: item.id,
        quantity: item.quantity,
        extras: item.extras?.length
          ? item.extras.map(id => ({id}))
          : undefined,
      }));
      await addItems(ticketId, items);
      setCart({});
      navigation.goBack();
    } catch (err) {
      const message =
        err instanceof Error ? err.message : 'No se pudo enviar.';
      setErrorsByView(prev => ({
        ...prev,
        [activeView]: message,
      }));
    } finally {
      setSubmitting(false);
    }
  };

  const renderItems = () => {
    if (loadingView[activeView]) {
      return (
        <View style={styles.loader}>
          <ActivityIndicator color="#fbbf24" />
        </View>
      );
    }
    if (!itemsToShow.length) {
      return <Text style={styles.emptyText}>No hay productos disponibles.</Text>;
    }
    return itemsToShow.map(entry => {
      const item = entry.item;
      const key = `${activeView}-${item.id}`;
      const extrasByGroup = groupExtras(item.extras ?? []);
      const hasExtras = Object.keys(extrasByGroup).length > 0;
      const cartItem: CartItem = {
        key,
        id: item.id,
        name: item.name,
        price: Number(item.price ?? 0),
        quantity: cart[key]?.quantity ?? 0,
        type: VIEW_TO_TYPE[activeView],
        extras: cart[key]?.extras ?? [],
      };
      const upsells = item.upsells ?? [];
      const hasUpsells = upsells.length > 0;
      const hasOptions = hasExtras || hasUpsells;
      const selectedCount = cartItem.extras.length;
      return (
        <PosItemCard
          key={key}
          title={item.name}
          priceLabel={`$${Number(item.price ?? 0).toFixed(2)}`}
          categoryLabel={hasSearch ? entry.categoryName : undefined}
          quantity={cartItem.quantity}
          hasExtras={hasOptions}
          selectedExtrasCount={selectedCount}
          expanded={expandedItemKey === key}
          isWide={isWide}
          onAdd={() => addItem(cartItem, extrasByGroup)}
          onRemove={() => removeItem(key)}
          onToggleOptions={() =>
            hasOptions
              ? setExpandedItemKey(prev => (prev === key ? null : key))
              : null
          }>
          {expandedItemKey === key && hasExtras ? (
            <View style={styles.optionsSection}>
              {Object.entries(extrasByGroup).map(([groupName, group]) => {
                const groupOptionIds = group.options.map(option => option.id);
                const maxSelect =
                  group.maxSelect ?? (group.kind === 'modifier' ? 1 : null);
                const requiredMin = Math.max(
                  group.required ? 1 : 0,
                  group.minSelect ?? 0,
                );
                return (
                  <View key={`${key}-${groupName}`} style={styles.optionGroup}>
                    <View style={styles.optionHeader}>
                      <Text style={styles.optionTitle}>{groupName}</Text>
                      {requiredMin || maxSelect ? (
                        <Text style={styles.optionMeta}>
                          {requiredMin ? `Min ${requiredMin}` : null}
                          {requiredMin && maxSelect ? ' · ' : null}
                          {maxSelect ? `Max ${maxSelect}` : null}
                        </Text>
                      ) : null}
                    </View>
                    <View style={styles.optionRow}>
                      {group.options.map(option => {
                        const isSelected = cartItem.extras.includes(option.id);
                        return (
                          <TouchableOpacity
                            key={`${key}-option-${option.id}`}
                            style={[
                              styles.optionChip,
                              isSelected && styles.optionChipActive,
                            ]}
                            onPress={() =>
                              toggleExtra(
                                cartItem,
                                option,
                                groupOptionIds,
                                maxSelect,
                              )
                            }>
                            <Text
                              style={[
                                styles.optionText,
                                isSelected && styles.optionTextActive,
                              ]}>
                              {option.name}
                            </Text>
                          </TouchableOpacity>
                        );
                      })}
                    </View>
                  </View>
                );
              })}
            </View>
          ) : null}
          {expandedItemKey === key && hasUpsells ? (
            <View style={styles.upsellSection}>
              <Text style={styles.upsellTitle}>Combínalo con</Text>
              <View style={styles.upsellRow}>
                {upsells.map(upsell => (
                  <TouchableOpacity
                    key={`${key}-upsell-${upsell.type}-${upsell.id}`}
                    style={styles.upsellChip}
                    onPress={() => handleUpsellPress(upsell)}>
                    <Text style={styles.upsellChipText}>{upsell.name}</Text>
                    <Text style={styles.upsellChipMeta}>
                      {UPSELL_TYPE_LABEL[upsell.type]} · $
                      {Number(upsell.price ?? 0).toFixed(2)}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>
            </View>
          ) : null}
        </PosItemCard>
      );
    });
  };

  const renderCategoryList = (mode: 'chip' | 'list') => {
    if (!categories.length) {
      return null;
    }
    return categories.map(category => {
      const isActive = category.id === activeCategoryId;
      return (
        <TouchableOpacity
          key={`category-${category.id}`}
          style={[
            mode === 'chip' ? styles.categoryChip : styles.categoryItem,
            isActive &&
              (mode === 'chip'
                ? styles.categoryChipActive
                : styles.categoryItemActive),
          ]}
          onPress={() =>
            setActiveCategoryByView(prev => ({
              ...prev,
              [activeView]: category.id,
            }))
          }>
          <Text
            style={[
              mode === 'chip' ? styles.categoryChipText : styles.categoryText,
              isActive &&
                (mode === 'chip'
                  ? styles.categoryChipTextActive
                  : styles.categoryTextActive),
            ]}>
            {category.name}
          </Text>
          {mode === 'list' ? (
            <Text style={styles.categoryMeta}>
              {(category.dishes ?? []).length} productos
            </Text>
          ) : null}
        </TouchableOpacity>
      );
    });
  };

  const renderCartPanel = (mode: 'panel' | 'bar') => {
    if (mode === 'bar') {
      return (
        <View style={styles.cartBar}>
          <Text style={styles.cartText}>
            {totals.count} items · ${totals.total.toFixed(2)}
          </Text>
          <TouchableOpacity
            style={[
              styles.submitButton,
              (totals.count === 0 || submitting) && styles.submitDisabled,
            ]}
            disabled={totals.count === 0 || submitting}
            onPress={handleSubmit}>
            {submitting ? (
              <ActivityIndicator color="#0f172a" />
            ) : (
              <Text style={styles.submitText}>Enviar</Text>
            )}
          </TouchableOpacity>
        </View>
      );
    }

    return (
      <View style={styles.cartPanel}>
        <Text style={styles.cartTitle}>Orden actual</Text>
        <ScrollView
          style={styles.cartList}
          contentContainerStyle={styles.cartListContent}>
          {cartItems.length ? (
            cartItems.map(item => {
              const extrasLabel = (item.extras ?? [])
                .map(id => extrasLookup.get(id)?.name)
                .filter(Boolean)
                .join(', ');
              return (
                <View key={`cart-${item.key}`} style={styles.cartItem}>
                  <View style={styles.cartItemInfo}>
                    <Text style={styles.cartItemName}>{item.name}</Text>
                    {extrasLabel ? (
                      <Text style={styles.cartItemExtras}>{extrasLabel}</Text>
                    ) : null}
                  </View>
                  <View style={styles.cartItemActions}>
                    <TouchableOpacity
                      style={styles.cartQtyButton}
                      onPress={() => removeItem(item.key)}>
                      <Text style={styles.cartQtyText}>-</Text>
                    </TouchableOpacity>
                    <Text style={styles.cartQtyValue}>{item.quantity}</Text>
                    <TouchableOpacity
                      style={styles.cartQtyButton}
                      onPress={() => incrementItem(item.key)}>
                      <Text style={styles.cartQtyText}>+</Text>
                    </TouchableOpacity>
                  </View>
                </View>
              );
            })
          ) : (
            <Text style={styles.emptyText}>No hay items en la orden.</Text>
          )}
        </ScrollView>
        <View style={styles.cartSummary}>
          <Text style={styles.cartSummaryText}>
            Total · ${totals.total.toFixed(2)}
          </Text>
          <TouchableOpacity
            style={[
              styles.submitButton,
              (totals.count === 0 || submitting) && styles.submitDisabled,
            ]}
            disabled={totals.count === 0 || submitting}
            onPress={handleSubmit}>
            {submitting ? (
              <ActivityIndicator color="#0f172a" />
            ) : (
              <Text style={styles.submitText}>Enviar</Text>
            )}
          </TouchableOpacity>
        </View>
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.container}>
        <View style={styles.headerCard}>
          <Text style={styles.heading}>{headerTitle}</Text>
          <Text style={styles.subheading}>
            Selecciona categorias y agrega productos rapido.
          </Text>
        </View>

        <View style={styles.tabs}>
          {(Object.keys(VIEW_LABELS) as ManagerView[]).map(view => (
            <TouchableOpacity
              key={view}
              style={[styles.tab, activeView === view && styles.tabActive]}
              onPress={() => setActiveView(view)}>
              <Text
                style={[
                  styles.tabText,
                  activeView === view && styles.tabTextActive,
                ]}>
                {VIEW_LABELS[view]}
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        <TextInput
          style={styles.input}
          placeholder="Buscar producto"
          placeholderTextColor="#94a3b8"
          value={searchTerm}
          onChangeText={setSearchTerm}
        />

        {errorsByView[activeView] ? (
          <Text style={styles.error}>{errorsByView[activeView]}</Text>
        ) : null}

        {isWide ? (
          <View style={styles.bodyRow}>
            <View style={styles.categoryPanel}>
              <Text style={styles.panelTitle}>Categorias</Text>
              <ScrollView
                style={styles.categoryScroll}
                contentContainerStyle={styles.categoryList}>
                {renderCategoryList('list')}
              </ScrollView>
            </View>
            <View style={styles.itemsPanel}>
              <View style={styles.itemsHeader}>
                <Text style={styles.panelTitle}>
                  {hasSearch ? 'Resultados' : 'Productos'}
                </Text>
              </View>
              <ScrollView
                style={styles.itemsScroll}
                contentContainerStyle={styles.itemsGrid}>
                {renderItems()}
              </ScrollView>
            </View>
            {renderCartPanel('panel')}
          </View>
        ) : (
          <>
            <ScrollView
              horizontal
              showsHorizontalScrollIndicator={false}
              contentContainerStyle={styles.categoryChips}>
              {renderCategoryList('chip')}
            </ScrollView>
            <ScrollView
              style={styles.itemsScroll}
              contentContainerStyle={styles.itemsStack}>
              {renderItems()}
            </ScrollView>
            {renderCartPanel('bar')}
          </>
        )}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#020617',
  },
  container: {
    flex: 1,
    padding: 20,
    gap: 12,
  },
  headerCard: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1e293b',
    gap: 6,
  },
  heading: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 16,
  },
  subheading: {
    color: '#94a3b8',
    fontSize: 12,
  },
  tabs: {
    flexDirection: 'row',
    backgroundColor: '#0b1220',
    borderRadius: 999,
    padding: 6,
    borderWidth: 1,
    borderColor: '#1f2937',
  },
  tab: {
    flex: 1,
    borderRadius: 999,
    paddingVertical: 8,
    alignItems: 'center',
  },
  tabActive: {
    backgroundColor: '#fbbf24',
  },
  tabText: {
    color: '#94a3b8',
    fontWeight: '600',
    fontSize: 12,
  },
  tabTextActive: {
    color: '#0f172a',
  },
  input: {
    backgroundColor: '#1e293b',
    borderRadius: 16,
    paddingHorizontal: 16,
    paddingVertical: 10,
    color: '#f8fafc',
    fontSize: 14,
  },
  bodyRow: {
    flex: 1,
    flexDirection: 'row',
    gap: 16,
  },
  panelTitle: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 14,
    marginBottom: 10,
  },
  categoryPanel: {
    width: 220,
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 12,
    borderWidth: 1,
    borderColor: '#1f2937',
  },
  categoryScroll: {
    flex: 1,
  },
  categoryList: {
    gap: 8,
    paddingBottom: 12,
  },
  categoryItem: {
    padding: 12,
    borderRadius: 14,
    backgroundColor: '#0b1220',
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 4,
  },
  categoryItemActive: {
    borderColor: '#fbbf24',
    backgroundColor: '#1f2937',
  },
  categoryText: {
    color: '#e2e8f0',
    fontWeight: '600',
    fontSize: 12,
  },
  categoryTextActive: {
    color: '#fbbf24',
  },
  categoryMeta: {
    color: '#94a3b8',
    fontSize: 11,
  },
  categoryChips: {
    gap: 8,
    paddingVertical: 6,
  },
  categoryChip: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#1f2937',
    paddingHorizontal: 14,
    paddingVertical: 8,
    backgroundColor: '#0f172a',
  },
  categoryChipActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  categoryChipText: {
    color: '#e2e8f0',
    fontWeight: '600',
    fontSize: 12,
  },
  categoryChipTextActive: {
    color: '#0f172a',
  },
  itemsPanel: {
    flex: 1,
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 12,
    borderWidth: 1,
    borderColor: '#1f2937',
  },
  itemsHeader: {
    marginBottom: 8,
  },
  itemsScroll: {
    flex: 1,
  },
  itemsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
    paddingBottom: 12,
  },
  itemsStack: {
    gap: 12,
    paddingBottom: 120,
  },
  itemCard: {
    backgroundColor: '#0b1220',
    borderRadius: 16,
    padding: 12,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 10,
    width: '100%',
  },
  itemCardWide: {
    flexBasis: '48%',
  },
  itemInfo: {
    gap: 4,
  },
  itemName: {
    color: '#f8fafc',
    fontWeight: '600',
  },
  itemCategory: {
    color: '#38bdf8',
    fontSize: 11,
    fontWeight: '700',
  },
  itemPrice: {
    color: '#94a3b8',
    fontSize: 12,
  },
  optionHint: {
    color: '#38bdf8',
    fontSize: 11,
    marginTop: 4,
    fontWeight: '600',
  },
  itemActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    justifyContent: 'flex-end',
  },
  qtyButton: {
    width: 36,
    height: 36,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#334155',
    alignItems: 'center',
    justifyContent: 'center',
  },
  qtyButtonText: {
    color: '#f8fafc',
    fontWeight: '700',
  },
  qtyValue: {
    color: '#f8fafc',
    fontWeight: '700',
    minWidth: 20,
    textAlign: 'center',
  },
  optionsSection: {
    backgroundColor: '#0b1220',
    borderRadius: 16,
    padding: 12,
    gap: 10,
  },
  optionGroup: {
    gap: 8,
  },
  optionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 8,
  },
  optionTitle: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 13,
  },
  optionMeta: {
    color: '#fbbf24',
    fontSize: 11,
    fontWeight: '700',
  },
  optionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  optionChip: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 10,
    paddingVertical: 6,
  },
  optionChipActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  optionText: {
    color: '#cbd5f5',
    fontWeight: '600',
    fontSize: 12,
  },
  optionTextActive: {
    color: '#0f172a',
  },
  upsellSection: {
    gap: 8,
    paddingTop: 6,
  },
  upsellTitle: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 12,
  },
  upsellRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  upsellChip: {
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 10,
    paddingVertical: 6,
    backgroundColor: '#0b1220',
  },
  upsellChipText: {
    color: '#f8fafc',
    fontWeight: '600',
    fontSize: 12,
  },
  upsellChipMeta: {
    color: '#94a3b8',
    fontSize: 10,
    marginTop: 2,
  },
  loader: {
    paddingVertical: 20,
  },
  emptyText: {
    color: '#94a3b8',
    textAlign: 'center',
    marginTop: 20,
  },
  cartPanel: {
    width: 280,
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 12,
    borderWidth: 1,
    borderColor: '#1f2937',
  },
  cartTitle: {
    color: '#f8fafc',
    fontWeight: '700',
    fontSize: 14,
    marginBottom: 10,
  },
  cartList: {
    flex: 1,
  },
  cartListContent: {
    gap: 10,
    paddingBottom: 12,
  },
  cartItem: {
    backgroundColor: '#0b1220',
    borderRadius: 14,
    padding: 10,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 8,
  },
  cartItemInfo: {
    gap: 4,
  },
  cartItemName: {
    color: '#f8fafc',
    fontWeight: '600',
    fontSize: 13,
  },
  cartItemExtras: {
    color: '#94a3b8',
    fontSize: 11,
  },
  cartItemActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    justifyContent: 'flex-end',
  },
  cartQtyButton: {
    width: 28,
    height: 28,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: '#334155',
    alignItems: 'center',
    justifyContent: 'center',
  },
  cartQtyText: {
    color: '#f8fafc',
    fontWeight: '700',
  },
  cartQtyValue: {
    color: '#f8fafc',
    fontWeight: '700',
    minWidth: 18,
    textAlign: 'center',
  },
  cartSummary: {
    borderTopWidth: 1,
    borderTopColor: '#1f2937',
    paddingTop: 12,
    gap: 10,
  },
  cartSummaryText: {
    color: '#f8fafc',
    fontWeight: '700',
  },
  cartBar: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
    marginTop: 12,
  },
  cartText: {
    color: '#f8fafc',
    fontWeight: '600',
  },
  submitButton: {
    backgroundColor: '#22c55e',
    borderRadius: 999,
    paddingHorizontal: 16,
    paddingVertical: 10,
  },
  submitDisabled: {
    opacity: 0.5,
  },
  submitText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  error: {
    color: '#fb7185',
    fontSize: 13,
  },
});

export default PosTakeOrderScreen;
