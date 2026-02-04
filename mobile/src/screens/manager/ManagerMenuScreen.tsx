import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  Platform,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';
import {useAuth} from '../../context/AuthContext';
import {
  createCategory,
  createManagedItem,
  deleteCategory,
  deleteManagedItem,
  getMobileViewSettings,
  getManagerExtras,
  getManagerPrepLabels,
  getManagerTaxes,
  getManagedCategories,
  reorderCategories,
  toggleManagedItem,
  updateCategory,
  updateManagedItem,
} from '../../services/api';
import {
  CategoryFormInput,
  CategoryPayload,
  Dish,
  DishFormInput,
  ExtraOption,
  ManagerMenuView,
  PrepLabel,
  Tax,
  ViewSetting,
} from '../../types';
import DishFormModal from '../../components/DishFormModal';
import CategoryFormModal from '../../components/CategoryFormModal';

type ViewMeta = {
  key: ManagerMenuView;
  label: string;
  description: string;
  itemLabel: string;
  actionLabel: string;
};

const VIEW_META: Record<ManagerMenuView, ViewMeta> = {
  menu: {
    key: 'menu',
    label: 'Menú',
    description:
      'Administra los platos principales y sus categorías visibles al cliente.',
    itemLabel: 'plato',
    actionLabel: '+ Nuevo plato',
  },
  wines: {
    key: 'wines',
    label: 'Café',
    description:
      'Controla la vista de café/brunch incluyendo bebidas destacadas.',
    itemLabel: 'bebida',
    actionLabel: '+ Nueva bebida',
  },
  cocktails: {
    key: 'cocktails',
    label: 'Bebidas',
    description:
      'Gestiona la sección de bebidas y sus recomendaciones especiales.',
    itemLabel: 'cóctel',
    actionLabel: '+ Nuevo cóctel',
  },
  cantina: {
    key: 'cantina',
    label: 'Cantina',
    description:
      'Administra la carta de cantina y los productos visibles al público.',
    itemLabel: 'artículo',
    actionLabel: '+ Nuevo artículo',
  },
};

type ViewStatus = {
  loading: boolean;
  refreshing: boolean;
  error: string | null;
};

const INITIAL_STATUS: Record<ManagerMenuView, ViewStatus> = {
  menu: {loading: true, refreshing: false, error: null},
  wines: {loading: false, refreshing: false, error: null},
  cocktails: {loading: false, refreshing: false, error: null},
  cantina: {loading: false, refreshing: false, error: null},
};

const VIEW_SCOPE: Record<ManagerMenuView, string> = {
  menu: 'menu',
  cocktails: 'cocktails',
  wines: 'coffee',
  cantina: 'cantina',
};

const SECTION_MODES = [
  {key: 'dishes', label: 'Platos'},
  {key: 'categories', label: 'Categorías'},
] as const;

type SectionMode = (typeof SECTION_MODES)[number]['key'];

const buildDefaultViewSettings = () =>
  (Object.values(VIEW_META) as ViewMeta[]).reduce<Record<
    ManagerMenuView,
    ViewSetting
  >>((acc, meta) => {
    acc[meta.key] = {label: meta.label, enabled: true};
    return acc;
  }, {} as Record<ManagerMenuView, ViewSetting>);

const ManagerMenuScreen = () => {
  const {token} = useAuth();
  const [activeView, setActiveView] = useState<ManagerMenuView>('menu');
  const [categoriesByView, setCategoriesByView] = useState<
    Record<ManagerMenuView, CategoryPayload[]>
  >({
    menu: [],
    wines: [],
    cocktails: [],
    cantina: [],
  });
  const [extrasByView, setExtrasByView] = useState<
    Record<ManagerMenuView, ExtraOption[]>
  >({
    menu: [],
    wines: [],
    cocktails: [],
    cantina: [],
  });
  const [prepLabels, setPrepLabels] = useState<PrepLabel[]>([]);
  const [taxes, setTaxes] = useState<Tax[]>([]);
  const [viewSettings, setViewSettings] = useState(buildDefaultViewSettings);
  const [statusByView, setStatusByView] =
    useState<Record<ManagerMenuView, ViewStatus>>(INITIAL_STATUS);
  const [modalVisible, setModalVisible] = useState(false);
  const [editingDish, setEditingDish] = useState<Dish | null>(null);
  const [defaultCategoryId, setDefaultCategoryId] = useState<
    number | undefined
  >();
  const [formLoading, setFormLoading] = useState(false);
  const [sectionMode, setSectionMode] = useState<SectionMode>('dishes');
  const [categoryModalVisible, setCategoryModalVisible] = useState(false);
  const [editingCategory, setEditingCategory] = useState<CategoryPayload | null>(
    null,
  );
  const [categoryFormLoading, setCategoryFormLoading] = useState(false);
  const [reorderingCategories, setReorderingCategories] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');

  const updateStatus = useCallback(
    (view: ManagerMenuView, patch: Partial<ViewStatus>) => {
      setStatusByView(prev => ({
        ...prev,
        [view]: {...prev[view], ...patch},
      }));
    },
    [],
  );

  const loadData = useCallback(
    async (view: ManagerMenuView, showLoader = true) => {
      if (!token) {
        return;
      }
      try {
        if (showLoader) {
          updateStatus(view, {loading: true});
        }
        const categoryData = await getManagedCategories(token, view);
        setCategoriesByView(prev => ({
          ...prev,
          [view]: categoryData,
        }));
        updateStatus(view, {error: null});
      } catch (err) {
        updateStatus(view, {
          error:
            err instanceof Error
              ? err.message
              : 'No se pudieron cargar las vistas.',
        });
      } finally {
        if (showLoader) {
          updateStatus(view, {loading: false});
        }
        updateStatus(view, {refreshing: false});
      }
    },
    [token, updateStatus],
  );

  const loadExtras = useCallback(
    async (view: ManagerMenuView) => {
      if (!token) {
        return;
      }
      try {
        const data = await getManagerExtras(token, VIEW_SCOPE[view], true);
        setExtrasByView(prev => ({...prev, [view]: data}));
      } catch (err) {
        Alert.alert(
          'Extras',
          err instanceof Error ? err.message : 'No se pudieron cargar.',
        );
      }
    },
    [token],
  );

  const loadPrepLabels = useCallback(async () => {
    if (!token) {
      return;
    }
    try {
      const data = await getManagerPrepLabels(token);
      setPrepLabels(data);
    } catch (err) {
      Alert.alert(
        'Preparación',
        err instanceof Error ? err.message : 'No se pudieron cargar labels.',
      );
    }
  }, [token]);

  const loadTaxes = useCallback(async () => {
    if (!token) {
      return;
    }
    try {
      const data = await getManagerTaxes(token);
      setTaxes(data.filter(tax => tax.active));
    } catch (err) {
      Alert.alert(
        'Impuestos',
        err instanceof Error ? err.message : 'No se pudieron cargar.',
      );
    }
  }, [token]);

  const loadViewSettings = useCallback(async () => {
    if (!token) {
      return;
    }
    try {
      const data = await getMobileViewSettings(token);
      if (data?.views) {
        setViewSettings(prev => ({
          ...prev,
          ...data.views,
        }));
      }
    } catch (err) {
      // No bloqueamos la pantalla si no carga la config
    }
  }, [token]);

  useFocusEffect(
    useCallback(() => {
      loadViewSettings();
      loadData(activeView);
      loadExtras(activeView);
      if (!prepLabels.length) {
        loadPrepLabels();
      }
      if (!taxes.length) {
        loadTaxes();
      }
    }, [
      activeView,
      loadData,
      loadExtras,
      loadPrepLabels,
      loadTaxes,
      loadViewSettings,
      prepLabels.length,
      taxes.length,
    ]),
  );

  const handleChangeView = (view: ManagerMenuView) => {
    setActiveView(view);
    setSectionMode('dishes');
    if (!categoriesByView[view].length && !statusByView[view].loading) {
      loadData(view);
    }
    if (!extrasByView[view].length) {
      loadExtras(view);
    }
  };

  const handleChangeSection = (mode: SectionMode) => {
    setSectionMode(mode);
  };

  const openCreateModal = (categoryId: number) => {
    setEditingDish(null);
    setDefaultCategoryId(categoryId);
    setModalVisible(true);
  };

  const openEditModal = (dish: Dish) => {
    setEditingDish(dish);
    setDefaultCategoryId(dish.category_id);
    setModalVisible(true);
  };

  const handleSubmit = async (
    values: DishFormInput,
    image?: {uri: string; type?: string; name?: string},
  ) => {
    if (!token) {
      throw new Error('Sesión inválida, vuelve a iniciar.');
    }
    setFormLoading(true);
    const payload: DishFormInput = {
      ...values,
      category_id:
        values.category_id ||
        defaultCategoryId ||
        categoriesByView[activeView][0]?.id ||
        0,
    };
    try {
      if (editingDish) {
        await updateManagedItem(token, activeView, editingDish.id, payload, image);
      } else {
        await createManagedItem(token, activeView, payload, image);
      }
      await loadData(activeView, false);
    } finally {
      setFormLoading(false);
    }
  };

  const handleToggle = async (dish: Dish) => {
    if (!token) {
      return;
    }
    try {
      await toggleManagedItem(token, activeView, dish.id);
      await loadData(activeView, false);
    } catch (err) {
      Alert.alert(
        'Error',
        err instanceof Error ? err.message : 'No se pudo actualizar.',
      );
    }
  };

  const handleDelete = (dish: Dish) => {
    Alert.alert(
      `Eliminar ${VIEW_META[activeView].itemLabel}`,
      `¿Seguro que deseas eliminar "${dish.name}"?`,
      [
        {text: 'Cancelar', style: 'cancel'},
        {
          text: 'Eliminar',
          style: 'destructive',
          onPress: async () => {
            if (!token) {
              return;
            }
            try {
              await deleteManagedItem(token, activeView, dish.id);
              await loadData(activeView, false);
            } catch (err) {
              Alert.alert(
                'Error',
                err instanceof Error ? err.message : 'No se pudo eliminar.',
              );
            }
          },
        },
      ],
    );
  };

  const openCategoryModal = (category?: CategoryPayload) => {
    setEditingCategory(category ?? null);
    setCategoryModalVisible(true);
  };

  const handleCategorySubmit = async (values: CategoryFormInput) => {
    if (!token) {
      throw new Error('Sesión inválida, vuelve a iniciar.');
    }
    setCategoryFormLoading(true);
    try {
      if (editingCategory) {
        await updateCategory(token, activeView, editingCategory.id, values);
      } else {
        await createCategory(token, activeView, values);
      }
      await loadData(activeView, false);
    } finally {
      setCategoryFormLoading(false);
    }
  };

  const handleDeleteCategory = (category: CategoryPayload) => {
    Alert.alert(
      'Eliminar categoría',
      `¿Deseas eliminar "${category.name}"?`,
      [
        {text: 'Cancelar', style: 'cancel'},
        {
          text: 'Eliminar',
          style: 'destructive',
          onPress: async () => {
            if (!token) {
              return;
            }
            try {
              await deleteCategory(token, activeView, category.id);
              await loadData(activeView, false);
            } catch (err) {
              Alert.alert(
                'Error',
                err instanceof Error ? err.message : 'No se pudo eliminar.',
              );
            }
          },
        },
      ],
    );
  };

  const moveCategory = async (categoryId: number, direction: -1 | 1) => {
    const currentList = categoriesByView[activeView];
    const currentIndex = currentList.findIndex(cat => cat.id === categoryId);
    const targetIndex = currentIndex + direction;
    if (currentIndex === -1 || targetIndex < 0 || targetIndex >= currentList.length) {
      return;
    }
    const reordered = [...currentList];
    const temp = reordered[currentIndex];
    reordered[currentIndex] = reordered[targetIndex];
    reordered[targetIndex] = temp;
    setCategoriesByView(prev => ({
      ...prev,
      [activeView]: reordered,
    }));
    if (!token) {
      return;
    }
    try {
      setReorderingCategories(true);
      await reorderCategories(
        token,
        activeView,
        reordered.map(cat => cat.id),
      );
    } catch (err) {
      Alert.alert(
        'Error',
        err instanceof Error ? err.message : 'No se pudo reordenar.',
      );
      await loadData(activeView, false);
    } finally {
      setReorderingCategories(false);
    }
  };

  const currentStatus = statusByView[activeView];
  const categories = categoriesByView[activeView];
  const viewMeta = VIEW_META[activeView];
  const viewLabel = viewSettings[activeView]?.label ?? viewMeta.label;
  const isCategoriesMode = sectionMode === 'categories';
  const filteredCategories = useMemo(() => {
    const term = searchTerm.trim().toLowerCase();
    if (!term) {
      return categories;
    }
    return categories
      .map(category => ({
        ...category,
        dishes: category.dishes.filter(dish => {
          const nameMatch = dish.name.toLowerCase().includes(term);
          const descMatch = dish.description.toLowerCase().includes(term);
          return nameMatch || descMatch;
        }),
      }))
      .filter(category => category.dishes.length > 0);
  }, [categories, searchTerm]);
  const displayCategories = isCategoriesMode ? categories : filteredCategories;
  const hasSearch = Boolean(searchTerm.trim());

  const renderCategoryCoverSummary = useCallback((category: CategoryPayload) => {
    if (!category.show_on_cover) {
      return 'No aparece en portada.';
    }
    const parts = [
      category.cover_title ? `Título: ${category.cover_title}` : null,
      category.cover_subtitle ? `Subtítulo: ${category.cover_subtitle}` : null,
    ].filter(Boolean);
    return parts.length ? parts.join(' · ') : 'Visible en portada.';
  }, []);

  const availableViews = useMemo(
    () =>
      (Object.values(VIEW_META) as ViewMeta[]).filter(
        meta => viewSettings[meta.key]?.enabled !== false,
      ),
    [viewSettings],
  );
  const viewTabs =
    availableViews.length > 0
      ? availableViews
      : (Object.values(VIEW_META) as ViewMeta[]);

  useEffect(() => {
    if (viewTabs.find(tab => tab.key === activeView)) {
      return;
    }
    setActiveView(viewTabs[0]?.key ?? 'menu');
  }, [activeView, viewTabs]);

  return (
    <>
      <ScrollView
        style={styles.container}
        contentContainerStyle={styles.content}
        refreshControl={
          <RefreshControl
            tintColor="#fbbf24"
            refreshing={currentStatus.refreshing}
            onRefresh={() => {
              updateStatus(activeView, {refreshing: true});
              loadData(activeView, false);
            }}
          />
        }>
        <View style={styles.card}>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.tabs}>
            {viewTabs.map(meta => {
              const focused = meta.key === activeView;
              const tabLabel = viewSettings[meta.key]?.label ?? meta.label;
              return (
                <TouchableOpacity
                  key={meta.key}
                  style={[styles.tabChip, focused && styles.tabChipActive]}
                  onPress={() => handleChangeView(meta.key)}>
                  <Text
                    style={[
                      styles.tabChipText,
                      focused && styles.tabChipTextActive,
                    ]}>
                    {tabLabel}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </ScrollView>
          <View style={styles.header}>
            <Text style={styles.heading}>{viewLabel}</Text>
            {currentStatus.loading && <ActivityIndicator color="#fbbf24" />}
          </View>
          <Text style={styles.subtitle}>{viewMeta.description}</Text>
          {currentStatus.error ? (
            <Text style={styles.error}>{currentStatus.error}</Text>
          ) : null}
          <View style={styles.sectionTabs}>
            {SECTION_MODES.map(mode => {
              const focused = mode.key === sectionMode;
              return (
                <TouchableOpacity
                  key={mode.key}
                  style={[styles.sectionChip, focused && styles.sectionChipActive]}
                  onPress={() => handleChangeSection(mode.key)}>
                  <Text
                    style={[
                      styles.sectionChipText,
                      focused && styles.sectionChipTextActive,
                    ]}>
                    {mode.label}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
          {!isCategoriesMode ? (
            <View style={styles.searchWrapper}>
              <TextInput
                style={styles.searchInput}
                placeholder={`Buscar ${viewMeta.itemLabel}s...`}
                placeholderTextColor="#475569"
                value={searchTerm}
                onChangeText={text => setSearchTerm(text)}
              />
              {hasSearch ? (
                <TouchableOpacity
                  style={styles.clearButton}
                  onPress={() => setSearchTerm('')}>
                  <Text style={styles.clearButtonText}>×</Text>
                </TouchableOpacity>
              ) : null}
            </View>
          ) : null}
        </View>

        {isCategoriesMode ? (
          <>
            <View style={[styles.card, styles.categoryActionsCard]}>
              <View style={styles.categoryActionsRow}>
                <TouchableOpacity
                  style={styles.addButton}
                  onPress={() => openCategoryModal()}>
                  <Text style={styles.addButtonText}>+ Nueva categoría</Text>
                </TouchableOpacity>
                {reorderingCategories ? (
                  <ActivityIndicator color="#fbbf24" />
                ) : null}
              </View>
              {categories.length === 0 ? (
                <Text style={styles.subtitle}>
                  Aún no tienes categorías. Crea la primera para comenzar.
                </Text>
              ) : null}
            </View>
            {categories.map((category, index) => (
              <View key={category.id} style={styles.card}>
                <View style={styles.categoryRow}>
                  <View style={{flex: 1}}>
                    <Text style={styles.categoryTitle}>{category.name}</Text>
                    <Text style={styles.categoryMeta}>
                      Orden {category.order} · {category.dishes.length} elementos
                    </Text>
                    <Text style={styles.coverMeta}>
                      {renderCategoryCoverSummary(category)}
                    </Text>
                  </View>
                  <View style={styles.categoryActionColumn}>
                    <TouchableOpacity
                      style={[
                        styles.reorderButton,
                        index === 0 && styles.reorderDisabled,
                      ]}
                      disabled={index === 0}
                      onPress={() => moveCategory(category.id, -1)}>
                      <Text style={styles.reorderText}>↑</Text>
                    </TouchableOpacity>
                    <TouchableOpacity
                      style={[
                        styles.reorderButton,
                        index === categories.length - 1 && styles.reorderDisabled,
                      ]}
                      disabled={index === categories.length - 1}
                      onPress={() => moveCategory(category.id, 1)}>
                      <Text style={styles.reorderText}>↓</Text>
                    </TouchableOpacity>
                  </View>
                </View>
                <View style={styles.categoryButtonsRow}>
                  <TouchableOpacity
                    style={styles.actionButton}
                    onPress={() => openCategoryModal(category)}>
                    <Text style={styles.actionText}>Editar</Text>
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={styles.secondaryActionButton}
                    onPress={() => openCreateModal(category.id)}>
                    <Text style={styles.secondaryActionText}>
                      + {viewMeta.itemLabel}
                    </Text>
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={styles.deleteButton}
                    onPress={() => handleDeleteCategory(category)}>
                    <Text style={styles.deleteText}>Eliminar</Text>
                  </TouchableOpacity>
                </View>
              </View>
            ))}
          </>
        ) : categories.length === 0 ? (
          <View style={styles.card}>
            <Text style={styles.subtitle}>
              No hay categorías configuradas para esta vista.
            </Text>
          </View>
        ) : hasSearch && displayCategories.length === 0 ? (
          <View style={styles.card}>
            <Text style={styles.subtitle}>
              No se encontraron {viewMeta.itemLabel}s para “{searchTerm.trim()}”.
            </Text>
          </View>
        ) : (
          displayCategories.map(category => (
            <View key={category.id} style={styles.card}>
              <View style={styles.categoryHeader}>
                <View>
                  <Text style={styles.categoryTitle}>{category.name}</Text>
                  <Text style={styles.categoryMeta}>
                    {category.dishes.length} elementos · Orden {category.order}
                  </Text>
                </View>
                <TouchableOpacity
                  style={styles.addButton}
                  onPress={() => openCreateModal(category.id)}>
                  <Text style={styles.addButtonText}>
                    {viewMeta.actionLabel}
                  </Text>
                </TouchableOpacity>
              </View>

              {category.dishes.length === 0 ? (
                <Text style={styles.subtitle}>
                  Aún no hay elementos en esta categoría.
                </Text>
              ) : (
                category.dishes.map(dish => (
                  <View key={dish.id} style={styles.dishRow}>
                    <View style={{flex: 1}}>
                      <View style={styles.dishHeader}>
                        <Text style={styles.dishName}>{dish.name}</Text>
                        <Text
                          style={[
                            styles.badge,
                            dish.visible ? styles.badgeSuccess : styles.badgeMuted,
                          ]}>
                          {dish.visible ? 'Visible' : 'Oculto'}
                        </Text>
                      </View>
                      <Text style={styles.dishDescription}>
                        {dish.description}
                      </Text>
                      <Text style={styles.dishMeta}>
                        ${dish.price.toFixed(2)} · Posición {dish.position}
                      </Text>
                    </View>
                    <View style={styles.actionColumn}>
                      <TouchableOpacity
                        style={styles.actionButton}
                        onPress={() => openEditModal(dish)}>
                        <Text style={styles.actionText}>Editar</Text>
                      </TouchableOpacity>
                      <TouchableOpacity
                        style={styles.actionButton}
                        onPress={() => handleToggle(dish)}>
                        <Text style={styles.actionText}>
                          {dish.visible ? 'Ocultar' : 'Mostrar'}
                        </Text>
                      </TouchableOpacity>
                      <TouchableOpacity
                        style={styles.deleteButton}
                        onPress={() => handleDelete(dish)}>
                        <Text style={styles.deleteText}>Eliminar</Text>
                      </TouchableOpacity>
                    </View>
                  </View>
                ))
              )}
            </View>
          ))
        )}
      </ScrollView>

      <DishFormModal
        visible={modalVisible}
        categories={categories}
        extras={extrasByView[activeView]}
        prepLabels={prepLabels}
        taxes={taxes}
        initialDish={editingDish}
        defaultCategoryId={defaultCategoryId}
        onClose={() => setModalVisible(false)}
        loading={formLoading}
        onSubmit={handleSubmit}
        viewLabel={viewLabel}
        itemLabel={viewMeta.itemLabel}
      />
      <CategoryFormModal
        visible={categoryModalVisible}
        initialCategory={editingCategory}
        onClose={() => setCategoryModalVisible(false)}
        onSubmit={handleCategorySubmit}
        loading={categoryFormLoading}
        viewLabel={viewLabel}
      />
    </>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#020617',
  },
  content: {
    padding: 20,
    gap: 16,
    paddingBottom: 40,
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 24,
    padding: 20,
    borderWidth: 1,
    borderColor: '#1e293b',
    gap: 12,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  heading: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
  subtitle: {
    color: '#94a3b8',
  },
  error: {
    color: '#fb7185',
  },
  sectionTabs: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 8,
  },
  sectionChip: {
    flex: 1,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#1e293b',
    paddingVertical: 10,
    alignItems: 'center',
    backgroundColor: '#020b1f',
  },
  sectionChipActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  sectionChipText: {
    color: '#94a3b8',
    fontWeight: '600',
  },
  sectionChipTextActive: {
    color: '#0f172a',
  },
  searchWrapper: {
    marginTop: 12,
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#1e293b',
    backgroundColor: '#020b1f',
    paddingHorizontal: 16,
  },
  searchInput: {
    flex: 1,
    color: '#f8fafc',
    paddingVertical: Platform.OS === 'ios' ? 12 : 8,
  },
  clearButton: {
    marginLeft: 8,
    width: 28,
    height: 28,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#1e293b',
  },
  clearButtonText: {
    color: '#f8fafc',
    fontSize: 18,
    lineHeight: 18,
  },
  categoryHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    flexWrap: 'wrap',
    gap: 12,
  },
  categoryTitle: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
  categoryMeta: {
    color: '#94a3b8',
    fontSize: 12,
  },
  coverMeta: {
    color: '#cbd5f5',
    fontSize: 12,
    marginTop: 6,
  },
  addButton: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#fbbf24',
    paddingHorizontal: 16,
    paddingVertical: 8,
  },
  addButtonText: {
    color: '#fbbf24',
    fontWeight: '600',
  },
  dishRow: {
    flexDirection: 'row',
    paddingVertical: 12,
    borderTopWidth: 1,
    borderTopColor: '#1e293b',
    gap: 12,
  },
  dishHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  dishName: {
    color: '#f8fafc',
    fontWeight: '600',
    fontSize: 16,
  },
  dishDescription: {
    color: '#94a3b8',
    fontSize: 13,
    marginTop: 6,
  },
  dishMeta: {
    color: '#cbd5f5',
    fontSize: 12,
    marginTop: 4,
  },
  actionColumn: {
    width: 110,
    gap: 8,
  },
  actionButton: {
    borderRadius: 12,
    paddingVertical: 8,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#334155',
  },
  actionText: {
    color: '#f8fafc',
    fontWeight: '600',
  },
  secondaryActionButton: {
    borderRadius: 12,
    paddingVertical: 8,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#fbbf24',
    flex: 1,
  },
  secondaryActionText: {
    color: '#fbbf24',
    fontWeight: '600',
  },
  deleteButton: {
    borderRadius: 12,
    paddingVertical: 8,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#fb7185',
  },
  deleteText: {
    color: '#fb7185',
    fontWeight: '600',
  },
  badge: {
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 4,
    fontSize: 11,
    fontWeight: '700',
  },
  badgeSuccess: {
    backgroundColor: 'rgba(16,185,129,0.15)',
    color: '#34d399',
  },
  badgeMuted: {
    backgroundColor: 'rgba(239,68,68,0.15)',
    color: '#f87171',
  },
  tabs: {
    gap: 12,
    paddingBottom: 4,
  },
  tabChip: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 18,
    paddingVertical: Platform.OS === 'ios' ? 10 : 8,
    backgroundColor: '#020b1f',
  },
  tabChipActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  tabChipText: {
    color: '#cbd5f5',
    fontWeight: '600',
  },
  tabChipTextActive: {
    color: '#0f172a',
  },
  categoryActionsCard: {
    gap: 0,
  },
  categoryActionsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 16,
  },
  categoryRow: {
    flexDirection: 'row',
    gap: 16,
  },
  categoryActionColumn: {
    justifyContent: 'flex-start',
    gap: 8,
  },
  reorderButton: {
    width: 36,
    height: 36,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#334155',
    alignItems: 'center',
    justifyContent: 'center',
  },
  reorderText: {
    color: '#f8fafc',
    fontWeight: '700',
  },
  reorderDisabled: {
    opacity: 0.4,
  },
  categoryButtonsRow: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 16,
    flexWrap: 'wrap',
  },
});

export default ManagerMenuScreen;
