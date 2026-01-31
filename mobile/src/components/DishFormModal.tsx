import React, {useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Modal,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {launchImageLibrary, Asset} from 'react-native-image-picker';
import {CategoryPayload, Dish, DishFormInput, ExtraOption, PrepLabel, Tax} from '../types';

type DishFormModalProps = {
  visible: boolean;
  categories: CategoryPayload[];
  extras?: ExtraOption[];
  prepLabels?: PrepLabel[];
  taxes?: Tax[];
  initialDish?: Dish | null;
  defaultCategoryId?: number;
  onClose: () => void;
  onSubmit: (values: DishFormInput, image?: {uri: string; type?: string; name?: string}) => Promise<void>;
  loading?: boolean;
  viewLabel?: string;
  itemLabel?: string;
};

const defaultForm = (categoryId?: number): DishFormInput => ({
  name: '',
  description: '',
  price: '',
  category_id: categoryId ?? 0,
  visible: true,
  featured_on_cover: false,
  recommended_dishes: [],
  extra_ids: [],
  prep_label_ids: [],
  tax_ids: [],
});

const DishFormModal: React.FC<DishFormModalProps> = ({
  visible,
  categories,
  extras = [],
  prepLabels = [],
  taxes = [],
  initialDish,
  defaultCategoryId,
  onClose,
  onSubmit,
  loading,
  viewLabel = 'vista',
  itemLabel = 'plato',
}) => {
  const [form, setForm] = useState<DishFormInput>(() =>
    defaultForm(categories[0]?.id),
  );
  const [image, setImage] = useState<Asset | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [recommendationsExpanded, setRecommendationsExpanded] = useState(false);
  const selectableCount = useMemo(() => {
    return categories.reduce((total, category) => {
      const options = category.dishes.filter(
        dish => !initialDish || dish.id !== initialDish.id,
      );
      return total + options.length;
    }, 0);
  }, [categories, initialDish]);

  useEffect(() => {
    if (initialDish) {
      setForm({
        name: initialDish.name,
        description: initialDish.description,
        price: String(initialDish.price),
        category_id: initialDish.category_id,
        visible: initialDish.visible,
        featured_on_cover: initialDish.featured_on_cover,
        recommended_dishes:
          initialDish.recommended_dishes?.map(d => d.id) ?? [],
        extra_ids: initialDish.extras?.map(extra => extra.id) ?? [],
        prep_label_ids:
          initialDish.prep_labels?.map(label => label.id) ?? [],
        tax_ids: initialDish.taxes?.map(tax => tax.id) ?? [],
      });
    } else if (categories.length) {
      setForm(defaultForm(defaultCategoryId ?? categories[0]?.id));
    }
    setImage(null);
    setError(null);
    setRecommendationsExpanded(false);
  }, [initialDish, categories, visible, defaultCategoryId]);

  const normalizedItemLabel = itemLabel.toLowerCase();

  const formTitle = useMemo(
    () =>
      initialDish
        ? `Editar ${normalizedItemLabel}`
        : `Nuevo ${normalizedItemLabel}`,
    [initialDish, normalizedItemLabel],
  );

  const handlePickImage = async () => {
    const res = await launchImageLibrary({
      mediaType: 'photo',
      selectionLimit: 1,
    });
    if (res.didCancel) {
      return;
    }
    if (res.errorCode) {
      setError('No se pudo cargar la imagen.');
      return;
    }
    const asset = res.assets?.[0];
    if (asset?.uri) {
      setImage(asset);
    }
  };

  const handleSubmit = async () => {
    if (!form.name.trim() || !form.description.trim()) {
      setError('Completa nombre y descripción.');
      return;
    }
    if (!form.price || Number.isNaN(Number(form.price))) {
      setError('El precio no es válido.');
      return;
    }
    if (!form.category_id) {
      setError('Selecciona una categoría.');
      return;
    }

    setSubmitting(true);
    setError(null);
    try {
      const imagePayload = image
        ? {
            uri: image.uri!,
            type: image.type ?? 'image/jpeg',
            name: image.fileName ?? `dish-${Date.now()}.jpg`,
          }
        : undefined;
      await onSubmit(form, imagePayload);
      onClose();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo guardar.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleRecommended = (dishId: number) => {
    setForm(prev => {
      const current = prev.recommended_dishes ?? [];
      const exists = current.includes(dishId);
      const updated = exists
        ? current.filter(id => id !== dishId)
        : [...current, dishId];
      return {
        ...prev,
        recommended_dishes: updated,
      };
    });
  };

  const isDishSelected = (dishId: number) =>
    (form.recommended_dishes ?? []).includes(dishId);

  const toggleIdSelection = (key: 'extra_ids' | 'prep_label_ids' | 'tax_ids', id: number) => {
    setForm(prev => {
      const current = prev[key] ?? [];
      const exists = current.includes(id);
      const updated = exists ? current.filter(value => value !== id) : [...current, id];
      return {
        ...prev,
        [key]: updated,
      };
    });
  };

  const isSelected = (key: 'extra_ids' | 'prep_label_ids' | 'tax_ids', id: number) =>
    (form[key] ?? []).includes(id);

  const formatTaxRate = (rate: number | string | null | undefined) => {
    const value = Number(rate);
    if (Number.isNaN(value)) {
      return '0.00';
    }
    return value.toFixed(2);
  };

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent
      onRequestClose={() => !submitting && onClose()}>
      <View style={styles.backdrop}>
        <View style={styles.modal}>
          <Text style={styles.title}>{formTitle}</Text>
          <ScrollView style={{flex: 1}} showsVerticalScrollIndicator={false}>
            <Text style={styles.label}>Nombre</Text>
            <TextInput
              style={styles.input}
              value={form.name}
              onChangeText={text => setForm(prev => ({...prev, name: text}))}
            />

            <Text style={styles.label}>Descripción</Text>
            <TextInput
              style={[styles.input, styles.textarea]}
              multiline
              value={form.description}
              onChangeText={text =>
                setForm(prev => ({...prev, description: text}))
              }
            />

            <Text style={styles.label}>Precio (USD)</Text>
            <TextInput
              style={styles.input}
              keyboardType="decimal-pad"
              value={form.price}
              onChangeText={text => setForm(prev => ({...prev, price: text}))}
            />

            <Text style={styles.label}>Categoría de {viewLabel}</Text>
            <View style={styles.dropdown}>
              {categories.map(category => (
                <TouchableOpacity
                  key={category.id}
                  style={[
                    styles.dropdownOption,
                    form.category_id === category.id && styles.dropdownOptionActive,
                  ]}
                  onPress={() =>
                    setForm(prev => ({...prev, category_id: category.id}))
                  }>
                  <Text
                    style={[
                      styles.dropdownText,
                      form.category_id === category.id && styles.dropdownTextActive,
                    ]}>
                    {category.name}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>

            <View style={styles.switchRow}>
              <TouchableOpacity
                style={[
                  styles.toggle,
                  form.visible && styles.toggleActive,
                ]}
                onPress={() => setForm(prev => ({...prev, visible: !prev.visible}))}>
                <Text
                  style={[
                    styles.toggleText,
                    form.visible && styles.toggleTextActive,
                  ]}>
                  {form.visible ? 'Visible' : 'Oculto'}
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[
                  styles.toggle,
                  form.featured_on_cover && styles.toggleActive,
                ]}
                onPress={() =>
                  setForm(prev => ({
                    ...prev,
                    featured_on_cover: !prev.featured_on_cover,
                  }))
                }>
                <Text
                  style={[
                    styles.toggleText,
                    form.featured_on_cover && styles.toggleTextActive,
                  ]}>
                  {form.featured_on_cover ? 'Destacado' : 'Sin destacar'}
                </Text>
              </TouchableOpacity>
            </View>

            <TouchableOpacity style={styles.imagePicker} onPress={handlePickImage}>
              <Text style={styles.imagePickerText}>
                {image?.fileName
                  ? `Imagen: ${image.fileName}`
                  : initialDish?.image
                  ? 'Cambiar imagen'
                  : 'Seleccionar imagen'}
              </Text>
            </TouchableOpacity>

            {selectableCount > 0 ? (
              <View style={styles.recommendedWrapper}>
                <View style={styles.recommendedSummary}>
                  <View>
                    <Text style={styles.label}>Acompañantes sugeridos</Text>
                    <Text style={styles.helper}>
                      {form.recommended_dishes?.length
                        ? `${form.recommended_dishes.length} seleccionados`
                        : 'Sin acompañantes seleccionados'}
                    </Text>
                  </View>
                  <TouchableOpacity
                    style={styles.manageButton}
                    onPress={() =>
                      setRecommendationsExpanded(prev => !prev)
                    }>
                    <Text style={styles.manageButtonText}>
                      {recommendationsExpanded ? 'Ocultar' : 'Administrar'}
                    </Text>
                  </TouchableOpacity>
                </View>
                {recommendationsExpanded ? (
                  <View style={styles.recommendedList}>
                    {categories.map(category => {
                      const options = category.dishes.filter(
                        dish => !initialDish || dish.id !== initialDish.id,
                      );
                      if (!options.length) {
                        return null;
                      }
                      return (
                        <View key={category.id} style={styles.recommendedCategory}>
                          <Text style={styles.recommendedCategoryTitle}>
                            {category.name}
                          </Text>
                          <ScrollView
                            horizontal
                            showsHorizontalScrollIndicator={false}
                            contentContainerStyle={styles.recommendedRow}>
                            {options.map(dish => {
                              const selected = isDishSelected(dish.id);
                              return (
                                <TouchableOpacity
                                  key={dish.id}
                                  style={[
                                    styles.recommendedChip,
                                    selected && styles.recommendedChipActive,
                                  ]}
                                  onPress={() => handleToggleRecommended(dish.id)}>
                                  <Text
                                    style={[
                                      styles.recommendedChipText,
                                      selected && styles.recommendedChipTextActive,
                                    ]}>
                                    {dish.name}
                                  </Text>
                                </TouchableOpacity>
                              );
                            })}
                          </ScrollView>
                        </View>
                      );
                    })}
                  </View>
                ) : null}
              </View>
            ) : null}

            {extras.length ? (
              <View style={styles.selectionWrapper}>
                <Text style={styles.label}>Modificadores disponibles</Text>
                <Text style={styles.helper}>
                  Selecciona los modificadores que aplican a este {normalizedItemLabel}.
                </Text>
                <View style={styles.selectionRow}>
                  {extras.map(extra => {
                    const selected = isSelected('extra_ids', extra.id);
                    return (
                      <TouchableOpacity
                        key={`extra-${extra.id}`}
                        style={[
                          styles.selectionChip,
                          selected && styles.selectionChipActive,
                        ]}
                        onPress={() => toggleIdSelection('extra_ids', extra.id)}>
                        <Text
                          style={[
                            styles.selectionChipText,
                            selected && styles.selectionChipTextActive,
                          ]}>
                          {extra.name}
                        </Text>
                      </TouchableOpacity>
                    );
                  })}
                </View>
              </View>
            ) : null}

            {prepLabels.length ? (
              <View style={styles.selectionWrapper}>
                <Text style={styles.label}>Labels de preparación</Text>
                <Text style={styles.helper}>
                  Define a qué estación se envía este {normalizedItemLabel}.
                </Text>
                <View style={styles.selectionRow}>
                  {prepLabels.map(label => {
                    const selected = isSelected('prep_label_ids', label.id);
                    return (
                      <TouchableOpacity
                        key={`prep-${label.id}`}
                        style={[
                          styles.selectionChip,
                          selected && styles.selectionChipActive,
                        ]}
                        onPress={() =>
                          toggleIdSelection('prep_label_ids', label.id)
                        }>
                        <Text
                          style={[
                            styles.selectionChipText,
                            selected && styles.selectionChipTextActive,
                          ]}>
                          {label.name}
                        </Text>
                      </TouchableOpacity>
                    );
                  })}
                </View>
              </View>
            ) : null}

            {taxes.length ? (
              <View style={styles.selectionWrapper}>
                <Text style={styles.label}>Impuestos</Text>
                <Text style={styles.helper}>
                  Asigna los taxes que aplican a este {normalizedItemLabel}.
                </Text>
                <View style={styles.selectionRow}>
                  {taxes.map(tax => {
                    const selected = isSelected('tax_ids', tax.id);
                    return (
                      <TouchableOpacity
                        key={`tax-${tax.id}`}
                        style={[
                          styles.selectionChip,
                          selected && styles.selectionChipActive,
                        ]}
                        onPress={() => toggleIdSelection('tax_ids', tax.id)}>
                        <Text
                          style={[
                            styles.selectionChipText,
                            selected && styles.selectionChipTextActive,
                          ]}>
                          {tax.name} · {formatTaxRate(tax.rate)}%
                        </Text>
                      </TouchableOpacity>
                    );
                  })}
                </View>
              </View>
            ) : null}

            {error ? <Text style={styles.error}>{error}</Text> : null}
          </ScrollView>

          <View style={styles.actions}>
            <TouchableOpacity
              style={[styles.button, styles.secondary]}
              disabled={submitting}
              onPress={onClose}>
              <Text style={styles.secondaryText}>Cancelar</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.button, submitting && styles.buttonDisabled]}
              onPress={handleSubmit}
              disabled={submitting}>
              {submitting || loading ? (
                <ActivityIndicator color="#0f172a" />
              ) : (
                <Text style={styles.buttonText}>Guardar</Text>
              )}
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  backdrop: {
    flex: 1,
    backgroundColor: 'rgba(2, 6, 23, 0.9)',
    justifyContent: 'center',
    padding: 16,
  },
  modal: {
    backgroundColor: '#0f172a',
    borderRadius: 28,
    padding: 20,
    flex: 1,
  },
  title: {
    color: '#f8fafc',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 12,
  },
  label: {
    color: '#cbd5f5',
    marginBottom: 6,
    marginTop: 12,
  },
  helper: {
    color: '#94a3b8',
    fontSize: 13,
    marginBottom: 6,
  },
  recommendedWrapper: {
    marginTop: 16,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#1e293b',
    padding: 16,
    backgroundColor: '#0b1528',
    gap: 10,
  },
  recommendedSummary: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 12,
  },
  manageButton: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#fbbf24',
    paddingHorizontal: 14,
    paddingVertical: 8,
  },
  manageButtonText: {
    color: '#fbbf24',
    fontWeight: '600',
  },
  recommendedList: {
    gap: 12,
  },
  input: {
    backgroundColor: '#1e293b',
    color: '#f8fafc',
    borderRadius: 16,
    paddingHorizontal: 16,
    paddingVertical: Platform.OS === 'ios' ? 14 : 10,
    fontSize: 16,
  },
  textarea: {
    minHeight: 100,
    textAlignVertical: 'top',
  },
  dropdown: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#1e293b',
    overflow: 'hidden',
  },
  dropdownOption: {
    paddingVertical: 12,
    paddingHorizontal: 16,
  },
  dropdownOptionActive: {
    backgroundColor: '#fbbf24',
  },
  dropdownText: {
    color: '#94a3b8',
  },
  dropdownTextActive: {
    color: '#0f172a',
    fontWeight: '700',
  },
  switchRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 12,
    marginTop: 16,
  },
  toggle: {
    flex: 1,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#334155',
    alignItems: 'center',
    paddingVertical: 12,
  },
  toggleActive: {
    borderColor: '#fbbf24',
    backgroundColor: '#fbbf24',
  },
  toggleText: {
    color: '#cbd5f5',
    fontWeight: '600',
  },
  toggleTextActive: {
    color: '#0f172a',
  },
  imagePicker: {
    marginTop: 16,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#fbbf24',
    paddingVertical: 12,
    alignItems: 'center',
  },
  imagePickerText: {
    color: '#fbbf24',
    fontWeight: '600',
  },
  recommendedCategory: {
    marginTop: 12,
    gap: 6,
  },
  recommendedCategoryTitle: {
    color: '#cbd5f5',
    fontWeight: '600',
    fontSize: 14,
  },
  recommendedRow: {
    flexDirection: 'row',
    gap: 8,
  },
  recommendedChip: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 14,
    paddingVertical: 8,
    backgroundColor: '#020b1f',
  },
  recommendedChipActive: {
    borderColor: '#fbbf24',
    backgroundColor: '#fbbf24',
  },
  recommendedChipText: {
    color: '#94a3b8',
    fontWeight: '600',
  },
  recommendedChipTextActive: {
    color: '#0f172a',
  },
  selectionWrapper: {
    marginTop: 16,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#1e293b',
    padding: 16,
    backgroundColor: '#0b1528',
    gap: 10,
  },
  selectionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  selectionChip: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 14,
    paddingVertical: 8,
    backgroundColor: '#020b1f',
  },
  selectionChipActive: {
    borderColor: '#fbbf24',
    backgroundColor: '#fbbf24',
  },
  selectionChipText: {
    color: '#94a3b8',
    fontWeight: '600',
    fontSize: 12,
  },
  selectionChipTextActive: {
    color: '#0f172a',
  },
  actions: {
    flexDirection: 'row',
    marginTop: 16,
    gap: 12,
  },
  button: {
    flex: 1,
    borderRadius: 999,
    paddingVertical: 14,
    alignItems: 'center',
    backgroundColor: '#fbbf24',
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  buttonText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  secondary: {
    backgroundColor: 'transparent',
    borderWidth: 1,
    borderColor: '#334155',
  },
  secondaryText: {
    color: '#cbd5f5',
    fontWeight: '600',
  },
  error: {
    color: '#fb7185',
    marginTop: 12,
  },
});

export default DishFormModal;
