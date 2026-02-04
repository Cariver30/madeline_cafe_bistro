import React, {useCallback, useEffect, useMemo, useRef, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  Modal,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {useAuth} from '../../context/AuthContext';
import {useServerSessions} from '../../context/ServerSessionsContext';
import {ServerStackParamList} from '../../navigation/serverTypes';
import {
  confirmOrder as confirmOrderApi,
  createServerOrder,
  getServerMenuCategories,
  getMobileViewSettings,
} from '../../services/api';
import {PosItemCard} from '../../components/pos/PosItemCard';
import {
  CategoryPayload,
  ManagerView,
  ServerOrderItemPayload,
  ExtraOption,
  UpsellItem,
  ViewSetting,
} from '../../types';

type Props = NativeStackScreenProps<ServerStackParamList, 'TakeOrder'>;

type CartItem = {
  key: string;
  id: number;
  name: string;
  price: number;
  quantity: number;
  type: ServerOrderItemPayload['type'];
  extras: number[];
  notes?: string;
};

const VIEW_ORDER: ManagerView[] = ['menu', 'cocktails', 'wines', 'cantina'];

const DEFAULT_VIEW_SETTINGS: Record<ManagerView, ViewSetting> = {
  menu: {label: 'Menú', enabled: true},
  cocktails: {label: 'Cócteles', enabled: true},
  wines: {label: 'Bebidas', enabled: true},
  cantina: {label: 'Cantina', enabled: true},
};

const VIEW_TO_TYPE: Record<ManagerView, ServerOrderItemPayload['type']> = {
  menu: 'dish',
  cocktails: 'cocktail',
  wines: 'wine',
  cantina: 'cantina',
};

const TYPE_TO_VIEW: Record<ServerOrderItemPayload['type'], ManagerView> = {
  dish: 'menu',
  cocktail: 'cocktails',
  wine: 'wines',
  cantina: 'cantina',
};

const UPSELL_TYPE_LABEL: Record<ServerOrderItemPayload['type'], string> = {
  dish: 'Plato',
  cocktail: 'Cóctel',
  wine: 'Bebida',
  cantina: 'Cantina',
};

const TakeOrderScreen = ({route, navigation}: Props) => {
  const {token} = useAuth();
  const {sessionId} = route.params;
  const {getSessionById, loadSessions} = useServerSessions();
  const session = getSessionById(sessionId);
  const [viewSettings, setViewSettings] = useState<
    Record<ManagerView, ViewSetting>
  >(DEFAULT_VIEW_SETTINGS);
  const [activeView, setActiveView] = useState<ManagerView>('menu');
  const [activeCategoryByView, setActiveCategoryByView] = useState<
    Record<ManagerView, number | null>
  >({
    menu: null,
    cocktails: null,
    wines: null,
    cantina: null,
  });
  const [categoriesByView, setCategoriesByView] = useState<
    Record<ManagerView, CategoryPayload[]>
  >({
    menu: [],
    cocktails: [],
    wines: [],
    cantina: [],
  });
  const [loadingView, setLoadingView] = useState<Record<ManagerView, boolean>>({
    menu: false,
    cocktails: false,
    wines: false,
    cantina: false,
  });
  const [errorsByView, setErrorsByView] = useState<
    Record<ManagerView, string | null>
  >({
    menu: null,
    cocktails: null,
    wines: null,
    cantina: null,
  });
  const [searchTerm, setSearchTerm] = useState('');
  const [cart, setCart] = useState<Record<string, CartItem>>({});
  const [expandedItemKey, setExpandedItemKey] = useState<string | null>(null);
  const [reviewVisible, setReviewVisible] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const loadedViews = useRef<Record<ManagerView, boolean>>({
    menu: false,
    cocktails: false,
    wines: false,
    cantina: false,
  });
  const isMounted = useRef(true);

  const viewTabs = useMemo(
    () => VIEW_ORDER.filter(view => viewSettings[view]?.enabled !== false),
    [viewSettings],
  );

  const findItemByType = useCallback(
    (type: ServerOrderItemPayload['type'], id: number) => {
      const view = TYPE_TO_VIEW[type];
      const categories = categoriesByView[view] ?? [];
      return categories
        .flatMap(category => category.dishes ?? [])
        .find(candidate => candidate.id === id);
    },
    [categoriesByView],
  );

  useEffect(() => {
    return () => {
      isMounted.current = false;
    };
  }, []);

  useEffect(() => {
    if (!token) {
      return;
    }
    let cancelled = false;
    const loadSettings = async () => {
      try {
        const data = await getMobileViewSettings(token);
        if (cancelled || !isMounted.current) return;
        const merged = {
          ...DEFAULT_VIEW_SETTINGS,
          ...(data.views ?? {}),
        } as Record<ManagerView, ViewSetting>;
        setViewSettings(merged);
        const firstEnabled =
          VIEW_ORDER.find(view => merged[view]?.enabled !== false) ?? 'menu';
        setActiveView(firstEnabled);
      } catch {
        if (cancelled || !isMounted.current) return;
        setViewSettings(DEFAULT_VIEW_SETTINGS);
      }
    };
    void loadSettings();
    return () => {
      cancelled = true;
    };
  }, [token]);

  const loadView = useCallback(
    async (view: ManagerView, force = false) => {
      if (!token || (!force && loadedViews.current[view])) {
        return;
      }
      setLoadingView(prev => ({...prev, [view]: true}));
      try {
        const data = await getServerMenuCategories(token, view);
        if (!isMounted.current) return;
        setCategoriesByView(prev => ({...prev, [view]: data}));
        setActiveCategoryByView(prev => {
          const nonEmpty = data.filter(
            category => (category.dishes?.length ?? 0) > 0,
          );
          const current = prev[view];
          const exists = current
            ? nonEmpty.some(category => category.id === current)
            : false;
          const next = exists ? current : nonEmpty[0]?.id ?? null;
          return {...prev, [view]: next};
        });
        loadedViews.current[view] = true;
        setErrorsByView(prev => ({...prev, [view]: null}));
      } catch (error) {
        if (!isMounted.current) return;
        const message =
          error instanceof Error ? error.message : 'No se pudo cargar.';
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
    loadedViews.current = {
      menu: false,
      cocktails: false,
      wines: false,
      cantina: false,
    };
    setCategoriesByView({
      menu: [],
      cocktails: [],
      wines: [],
      cantina: [],
    });
    setErrorsByView({
      menu: null,
      cocktails: null,
      wines: null,
      cantina: null,
    });
    setActiveCategoryByView({
      menu: null,
      cocktails: null,
      wines: null,
      cantina: null,
    });

    viewTabs.forEach(view => {
      void loadView(view, true);
    });
  }, [token, loadView, viewTabs]);

  const categoriesForView = (categoriesByView[activeView] ?? []).filter(
    category => (category.dishes?.length ?? 0) > 0,
  );

  const filteredCategories = useMemo(() => {
    const categories = categoriesForView;
    const activeCategoryId = activeCategoryByView[activeView];
    const scopedCategories = activeCategoryId
      ? categories.filter(category => category.id === activeCategoryId)
      : categories;
    if (!searchTerm.trim()) {
      return scopedCategories;
    }
    const term = searchTerm.toLowerCase();
    return scopedCategories
      .map(category => ({
        ...category,
        dishes: (category.dishes ?? []).filter(item =>
          item.name.toLowerCase().includes(term),
        ),
      }))
      .filter(category => (category.dishes ?? []).length > 0);
  }, [categoriesForView, activeCategoryByView, activeView, searchTerm]);

  const toggleExpandItem = (key: string) => {
    setExpandedItemKey(prev => (prev === key ? null : key));
  };

  const addItem = (
    item: CartItem,
    extrasByGroup: Record<
      string,
      {kind: string; required: boolean; maxSelect: number | null; options: ExtraOption[]}
    >,
  ) => {
    const missingRequired = Object.entries(extrasByGroup)
      .filter(([, group]) => group.required)
      .filter(([_, group]) => {
        const optionIds = group.options.map(option => option.id);
        return !item.extras.some(id => optionIds.includes(id));
      })
      .map(([groupName]) => groupName);

    if (missingRequired.length) {
      Alert.alert(
        'Faltan opciones',
        `Selecciona las opciones requeridas: ${missingRequired.join(', ')}`,
      );
      setExpandedItemKey(item.key);
      setErrorsByView(prev => ({
        ...prev,
        [activeView]: `Selecciona las opciones requeridas: ${missingRequired.join(
          ', ',
        )}`,
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
          notes: existing?.notes ?? item.notes ?? undefined,
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

  const totals = useMemo(() => {
    const items = Object.values(cart);
    const count = items.reduce((sum, item) => sum + item.quantity, 0);
    const total = items.reduce(
      (sum, item) => sum + item.quantity * item.price,
      0,
    );
    return {count, total};
  }, [cart]);

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
    const target = findItemByType(upsell.type, upsell.id);
    const extrasByGroup = groupExtras(target?.extras ?? []);
    if (hasRequiredExtras(target?.extras ?? [])) {
      setActiveView(view);
      setSearchTerm('');
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
      notes: cart[key]?.notes,
    };

    addItem(cartItem, extrasByGroup);
  };

  const updateNotes = (key: string, notes: string) => {
    setCart(prev => {
      const existing = prev[key];
      if (!existing) {
        return prev;
      }
      return {
        ...prev,
        [key]: {
          ...existing,
          notes,
        },
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

  const toggleGroupSelection = (
    item: CartItem,
    groupOptionIds: number[],
    maxSelect: number | null,
  ) => {
    setCart(prev => {
      const existing = prev[item.key] ?? {...item, quantity: 1, extras: []};
      const selected = existing.extras ?? [];
      const selectedInGroup = selected.filter(id =>
        groupOptionIds.includes(id),
      );
      const limit = maxSelect ?? groupOptionIds.length;
      let nextExtras: number[];

      if (selectedInGroup.length >= limit) {
        nextExtras = selected.filter(id => !groupOptionIds.includes(id));
      } else {
        const toSelect = groupOptionIds.slice(0, limit);
        nextExtras = [
          ...selected.filter(id => !groupOptionIds.includes(id)),
          ...toSelect,
        ];
      }

      return {
        ...prev,
        [item.key]: {...existing, extras: nextExtras},
      };
    });
  };

  const validateRequiredExtras = useCallback(() => {
    if (!token || totals.count === 0) {
      return false;
    }
    const missingByItem: string[] = [];
    Object.values(cart).forEach(item => {
      const view = TYPE_TO_VIEW[item.type];
      const categories = categoriesByView[view] ?? [];
      const sourceItem = categories
        .flatMap(category => category.dishes ?? [])
        .find(candidate => candidate.id === item.id);
      if (!sourceItem) {
        return;
      }
      const groups = groupExtras(sourceItem.extras ?? []);
      const missingRequired = Object.entries(groups)
        .filter(([, group]) => group.required || (group.minSelect ?? 0) > 0)
        .filter(([, group]) => {
          const optionIds = group.options.map(option => option.id);
          const selectedCount = item.extras.filter(id =>
            optionIds.includes(id),
          ).length;
          const requiredMin = Math.max(
            group.required ? 1 : 0,
            group.minSelect ?? 0,
          );
          return selectedCount < requiredMin;
        })
        .map(([groupName]) => groupName);
      if (missingRequired.length) {
        missingByItem.push(
          `${sourceItem.name}: ${missingRequired.join(', ')}`,
        );
      }
    });

    if (missingByItem.length) {
      Alert.alert(
        'Faltan opciones',
        `Selecciona las opciones requeridas:\n${missingByItem.join(' · ')}`,
      );
      setErrorsByView(prev => ({
        ...prev,
        [activeView]: `Faltan opciones requeridas: ${missingByItem.join(' · ')}`,
      }));
      return false;
    }

    return true;
  }, [activeView, cart, categoriesByView, token, totals.count]);

  const openReview = () => {
    if (!validateRequiredExtras()) {
      return;
    }
    setReviewVisible(true);
  };

  const handleSubmit = async () => {
    if (!validateRequiredExtras()) {
      return;
    }

    setSubmitting(true);
    setErrorsByView(prev => ({...prev, [activeView]: null}));
    try {
      const items: ServerOrderItemPayload[] = Object.values(cart).map(item => ({
        type: item.type,
        id: item.id,
        quantity: item.quantity,
        notes: item.notes?.trim() ? item.notes.trim() : undefined,
        extras: item.extras?.length ? item.extras.map(id => ({id})) : undefined,
      }));
      const created = await createServerOrder(token, sessionId, items);
      // Confirm immediately so Clover receives the order and routes printers.
      await confirmOrderApi(token, created.batch_id);
      await loadSessions(false);
      setReviewVisible(false);
      navigation.goBack();
    } catch (error) {
      const message =
        error instanceof Error ? error.message : 'No se pudo enviar.';
      Alert.alert('Error al enviar', message);
      setErrorsByView(prev => ({
        ...prev,
        [activeView]: message,
      }));
    } finally {
      setSubmitting(false);
    }
  };

  const reviewItems = useMemo(
    () => Object.values(cart).filter(item => item.quantity > 0),
    [cart],
  );

  return (
    <View style={styles.container}>
      <ScrollView style={styles.scroll} contentContainerStyle={styles.content}>
      <View style={styles.headerCard}>
        <Text style={styles.heading}>
          Tomar orden {session ? `· Mesa ${session.table_label}` : ''}
        </Text>
        <Text style={styles.subheading}>
          Selecciona productos por categoría.
        </Text>
      </View>

      <View style={styles.tabs}>
        {viewTabs.map(view => (
          <TouchableOpacity
            key={view}
            style={[styles.tab, activeView === view && styles.tabActive]}
            onPress={() => setActiveView(view)}>
            <Text
              style={[
                styles.tabText,
                activeView === view && styles.tabTextActive,
              ]}>
              {viewSettings[view]?.label ??
                DEFAULT_VIEW_SETTINGS[view].label}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        style={styles.categoryTabs}
        contentContainerStyle={styles.categoryTabsContent}>
        <TouchableOpacity
          style={[
            styles.categoryTab,
            activeCategoryByView[activeView] === null && styles.categoryTabActive,
          ]}
          onPress={() =>
            setActiveCategoryByView(prev => ({...prev, [activeView]: null}))
          }>
          <Text
            style={[
              styles.categoryTabText,
              activeCategoryByView[activeView] === null && styles.categoryTabTextActive,
            ]}>
            Todas
          </Text>
        </TouchableOpacity>
        {categoriesForView.map(category => {
          const isActive = activeCategoryByView[activeView] === category.id;
          return (
            <TouchableOpacity
              key={`${activeView}-cat-${category.id}`}
              style={[styles.categoryTab, isActive && styles.categoryTabActive]}
              onPress={() =>
                setActiveCategoryByView(prev => ({
                  ...prev,
                  [activeView]: category.id,
                }))
              }>
              <Text
                style={[
                  styles.categoryTabText,
                  isActive && styles.categoryTabTextActive,
                ]}>
                {category.name}
              </Text>
            </TouchableOpacity>
          );
        })}
      </ScrollView>

      <TextInput
        style={styles.input}
        placeholder="Buscar producto"
        placeholderTextColor="#94a3b8"
        value={searchTerm}
        onChangeText={setSearchTerm}
      />

      {loadingView[activeView] ? (
        <View style={styles.loader}>
          <ActivityIndicator color="#fbbf24" />
        </View>
      ) : filteredCategories.length ? (
        filteredCategories.map(category => (
          <View key={`${activeView}-${category.id}`} style={styles.card}>
            <Text style={styles.categoryTitle}>{category.name}</Text>
            {(category.dishes ?? []).map(item => {
              const key = `${activeView}-${item.id}`;
              const expanded = expandedItemKey === key;
              const cartItem: CartItem = {
                key,
                id: item.id,
                name: item.name,
                price: Number(item.price ?? 0),
                quantity: cart[key]?.quantity ?? 0,
                type: VIEW_TO_TYPE[activeView],
                extras: cart[key]?.extras ?? [],
                notes: cart[key]?.notes,
              };
              const extrasByGroup = groupExtras(item.extras ?? []);
              const hasExtras = Object.keys(extrasByGroup).length > 0;
              const upsells = item.upsells ?? [];
              const hasUpsells = upsells.length > 0;
              const hasOptions = hasExtras || hasUpsells;
              const selectedCount = cartItem.extras.length;
              return (
                <PosItemCard
                  key={key}
                  title={item.name}
                  priceLabel={`$${Number(item.price ?? 0).toFixed(2)}`}
                  quantity={cartItem.quantity}
                  hasExtras={hasOptions}
                  selectedExtrasCount={selectedCount}
                  expanded={expanded}
                  isWide={false}
                  onAdd={() => addItem(cartItem, extrasByGroup)}
                  onRemove={() => removeItem(key)}
                  onToggleOptions={() =>
                    hasOptions ? toggleExpandItem(key) : null
                  }>
                  {expanded && hasExtras ? (
                    <View style={styles.optionsSection}>
                      {Object.entries(extrasByGroup).map(
                        ([groupName, group]) => {
                          const groupOptionIds = group.options.map(
                            option => option.id,
                          );
                          const maxSelect =
                            group.maxSelect ?? (group.kind === 'modifier' ? 1 : null);
                          const canSelectAll =
                            !maxSelect || maxSelect >= groupOptionIds.length;
                          const selectedInGroup = cartItem.extras.filter(id =>
                            groupOptionIds.includes(id),
                          ).length;
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
                                {canSelectAll && groupOptionIds.length > 1 ? (
                                  <TouchableOpacity
                                    onPress={() =>
                                      toggleGroupSelection(
                                        cartItem,
                                        groupOptionIds,
                                        null,
                                      )
                                    }>
                                    <Text style={styles.optionAction}>
                                      {selectedInGroup >= groupOptionIds.length
                                        ? 'Quitar'
                                        : 'Todo'}
                                    </Text>
                                  </TouchableOpacity>
                                ) : null}
                              </View>
                              <View style={styles.optionRow}>
                                {group.options.map(option => {
                                  const isSelected = cartItem.extras.includes(
                                    option.id,
                                  );
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
                        },
                      )}
                    </View>
                  ) : null}
                  {expanded ? (
                    <View style={styles.notesSection}>
                      <Text style={styles.notesLabel}>Notas para cocina</Text>
                      <TextInput
                        style={styles.notesInput}
                        placeholder="Escribe una nota (ej. sin cebolla)"
                        placeholderTextColor="#94a3b8"
                        value={cartItem.notes ?? ''}
                        onChangeText={text => updateNotes(cartItem.key, text)}
                        multiline
                      />
                    </View>
                  ) : null}
                  {expanded && hasUpsells ? (
                    <View style={styles.upsellSection}>
                      <Text style={styles.upsellTitle}>Combínalo con</Text>
                      <View style={styles.upsellRow}>
                        {upsells.map(upsell => (
                          <TouchableOpacity
                            key={`${key}-upsell-${upsell.type}-${upsell.id}`}
                            style={styles.upsellChip}
                            onPress={() => handleUpsellPress(upsell)}>
                            <Text style={styles.upsellChipText}>
                              {upsell.name}
                            </Text>
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
            })}
          </View>
        ))
      ) : (
        <Text style={styles.emptyText}>No hay productos disponibles.</Text>
      )}

      {errorsByView[activeView] ? (
        <View style={styles.errorBox}>
          <Text style={styles.error}>{errorsByView[activeView]}</Text>
          <TouchableOpacity
            style={styles.retryButton}
            onPress={() => loadView(activeView, true)}>
            <Text style={styles.retryText}>Reintentar</Text>
          </TouchableOpacity>
        </View>
      ) : null}

      </ScrollView>

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
          onPress={openReview}>
          {submitting ? (
            <ActivityIndicator color="#0f172a" />
          ) : (
            <Text style={styles.submitText}>Enviar orden</Text>
          )}
        </TouchableOpacity>
      </View>

      <Modal
        visible={reviewVisible}
        transparent
        animationType="slide"
        onRequestClose={() => setReviewVisible(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Confirmar orden</Text>
            <Text style={styles.modalSubtitle}>
              Revisa los detalles antes de enviar.
            </Text>
            <ScrollView style={styles.modalScroll}>
              {reviewItems.map(item => {
                const sourceItem = findItemByType(item.type, item.id);
                const extras = (sourceItem?.extras ?? []).filter(extra =>
                  item.extras.includes(extra.id),
                );
                const groupedExtras = extras.reduce(
                  (groups: Record<string, string[]>, extra) => {
                    const groupName = extra.group_name || 'Opciones';
                    if (!groups[groupName]) {
                      groups[groupName] = [];
                    }
                    groups[groupName].push(extra.name);
                    return groups;
                  },
                  {},
                );
                return (
                  <View key={`review-${item.key}`} style={styles.modalItem}>
                    <View style={styles.modalItemRow}>
                      <Text style={styles.modalItemName}>
                        {item.quantity}x {item.name}
                      </Text>
                      <Text style={styles.modalItemPrice}>
                        ${Number(item.price).toFixed(2)}
                      </Text>
                    </View>
                    {Object.keys(groupedExtras).length ? (
                      <View style={styles.modalExtras}>
                        {Object.entries(groupedExtras).map(
                          ([groupName, names]) => (
                            <Text
                              key={`${item.key}-${groupName}`}
                              style={styles.modalExtrasText}>
                              {groupName}: {names.join(', ')}
                            </Text>
                          ),
                        )}
                      </View>
                    ) : null}
                    {item.notes ? (
                      <Text style={styles.modalNotes}>
                        Nota: {item.notes}
                      </Text>
                    ) : null}
                  </View>
                );
              })}
            </ScrollView>
            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={styles.modalCancel}
                onPress={() => setReviewVisible(false)}
                disabled={submitting}>
                <Text style={styles.modalCancelText}>Cancelar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[
                  styles.modalConfirm,
                  submitting && styles.submitDisabled,
                ]}
                onPress={handleSubmit}
                disabled={submitting}>
                {submitting ? (
                  <ActivityIndicator color="#0f172a" />
                ) : (
                  <Text style={styles.modalConfirmText}>
                    Confirmar y enviar
                  </Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#020617',
  },
  scroll: {
    flex: 1,
  },
  content: {
    padding: 20,
    gap: 12,
    paddingBottom: 120,
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
    fontSize: 18,
  },
  subheading: {
    color: '#94a3b8',
    fontSize: 13,
  },
  tabs: {
    flexDirection: 'row',
    backgroundColor: '#0b1220',
    borderRadius: 999,
    padding: 6,
    borderWidth: 1,
    borderColor: '#1f2937',
  },
  categoryTabs: {
    marginTop: 2,
  },
  categoryTabsContent: {
    paddingRight: 8,
  },
  categoryTab: {
    paddingVertical: 8,
    paddingHorizontal: 14,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: 'rgba(148, 163, 184, 0.35)',
    backgroundColor: '#0b1324',
    marginRight: 8,
  },
  categoryTabActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  categoryTabText: {
    color: '#cbd5f5',
    fontWeight: '600',
    fontSize: 12,
  },
  categoryTabTextActive: {
    color: '#0f172a',
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
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    gap: 10,
  },
  itemCard: {
    borderBottomWidth: 1,
    borderBottomColor: '#1f2937',
    paddingBottom: 10,
    gap: 8,
  },
  categoryTitle: {
    color: '#fbbf24',
    fontWeight: '700',
    fontSize: 14,
  },
  itemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 10,
  },
  itemInfo: {
    flex: 1,
    paddingRight: 12,
  },
  itemName: {
    color: '#f8fafc',
    fontWeight: '600',
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
  },
  qtyButton: {
    width: 28,
    height: 28,
    borderRadius: 14,
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
  optionAction: {
    color: '#38bdf8',
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
  notesSection: {
    gap: 6,
    paddingTop: 6,
  },
  notesLabel: {
    color: '#e2e8f0',
    fontWeight: '700',
    fontSize: 12,
  },
  notesInput: {
    minHeight: 44,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 12,
    paddingVertical: 8,
    color: '#f8fafc',
    backgroundColor: '#0b1220',
    fontSize: 12,
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
  cartBar: {
    position: 'absolute',
    left: 16,
    right: 16,
    bottom: 16,
    backgroundColor: '#0f172a',
    borderRadius: 20,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
    shadowColor: '#000',
    shadowOpacity: 0.2,
    shadowRadius: 12,
    shadowOffset: {width: 0, height: 6},
    elevation: 8,
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
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(2, 6, 23, 0.7)',
    justifyContent: 'flex-end',
  },
  modalCard: {
    backgroundColor: '#0f172a',
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 20,
    maxHeight: '85%',
    borderWidth: 1,
    borderColor: '#1f2937',
  },
  modalTitle: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
  modalSubtitle: {
    color: '#94a3b8',
    fontSize: 13,
    marginTop: 4,
  },
  modalScroll: {
    marginTop: 16,
  },
  modalItem: {
    borderWidth: 1,
    borderColor: '#1f2937',
    borderRadius: 16,
    padding: 12,
    marginBottom: 10,
    backgroundColor: '#0b1220',
  },
  modalItemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 12,
  },
  modalItemName: {
    color: '#f8fafc',
    fontWeight: '700',
    flex: 1,
  },
  modalItemPrice: {
    color: '#fbbf24',
    fontWeight: '700',
  },
  modalExtras: {
    marginTop: 6,
    gap: 2,
  },
  modalExtrasText: {
    color: '#cbd5f5',
    fontSize: 12,
  },
  modalNotes: {
    color: '#94a3b8',
    fontSize: 12,
    marginTop: 6,
  },
  modalFooter: {
    marginTop: 12,
    flexDirection: 'row',
    gap: 12,
  },
  modalCancel: {
    flex: 1,
    borderWidth: 1,
    borderColor: '#334155',
    borderRadius: 999,
    paddingVertical: 12,
    alignItems: 'center',
  },
  modalCancelText: {
    color: '#e2e8f0',
    fontWeight: '600',
  },
  modalConfirm: {
    flex: 1,
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingVertical: 12,
    alignItems: 'center',
  },
  modalConfirmText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  error: {
    color: '#fb7185',
    fontSize: 14,
  },
  errorBox: {
    gap: 10,
    alignItems: 'flex-start',
  },
  retryButton: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#fb7185',
  },
  retryText: {
    color: '#fb7185',
    fontWeight: '600',
    fontSize: 12,
  },
});

export default TakeOrderScreen;
