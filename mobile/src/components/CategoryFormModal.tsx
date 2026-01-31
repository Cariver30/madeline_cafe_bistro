import React, {useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Modal,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {CategoryFormInput, CategoryPayload} from '../types';

type CategoryFormModalProps = {
  visible: boolean;
  initialCategory?: CategoryPayload | null;
  onClose: () => void;
  onSubmit: (values: CategoryFormInput) => Promise<void>;
  loading?: boolean;
  viewLabel?: string;
};

const defaultValues: CategoryFormInput = {
  name: '',
  show_on_cover: false,
  cover_title: '',
  cover_subtitle: '',
};

const CategoryFormModal: React.FC<CategoryFormModalProps> = ({
  visible,
  initialCategory,
  onClose,
  onSubmit,
  loading,
  viewLabel = 'vista',
}) => {
  const [form, setForm] = useState<CategoryFormInput>(defaultValues);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (initialCategory) {
      setForm({
        name: initialCategory.name,
        show_on_cover: initialCategory.show_on_cover ?? false,
        cover_title: initialCategory.cover_title ?? '',
        cover_subtitle: initialCategory.cover_subtitle ?? '',
      });
    } else {
      setForm(defaultValues);
    }
    setError(null);
  }, [initialCategory, visible]);

  const title = useMemo(
    () => (initialCategory ? 'Editar categoría' : 'Nueva categoría'),
    [initialCategory],
  );

  const handleSubmit = async () => {
    if (!form.name.trim()) {
      setError('La categoría debe tener un nombre.');
      return;
    }
    setSubmitting(true);
    setError(null);
    try {
      const payload: CategoryFormInput = {
        name: form.name.trim(),
        show_on_cover: form.show_on_cover,
        cover_title: form.cover_title?.trim() || null,
        cover_subtitle: form.cover_subtitle?.trim() || null,
      };
      await onSubmit(payload);
      onClose();
    } catch (err) {
      setError(
        err instanceof Error
          ? err.message
          : 'No se pudo guardar la categoría.',
      );
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent
      onRequestClose={() => !submitting && onClose()}>
      <View style={styles.backdrop}>
        <View style={styles.modal}>
          <Text style={styles.title}>
            {title} ({viewLabel})
          </Text>
          <ScrollView style={{flex: 1}} showsVerticalScrollIndicator={false}>
            <Text style={styles.label}>Nombre interno</Text>
            <TextInput
              style={styles.input}
              value={form.name}
              onChangeText={text => setForm(prev => ({...prev, name: text}))}
              placeholder="Ej. Brunch salado"
              placeholderTextColor="#64748b"
            />

            <View style={styles.toggleRow}>
              <Text style={styles.label}>Mostrar en portada</Text>
              <TouchableOpacity
                style={[
                  styles.togglePill,
                  form.show_on_cover && styles.togglePillActive,
                ]}
                onPress={() =>
                  setForm(prev => ({...prev, show_on_cover: !prev.show_on_cover}))
                }>
                <Text
                  style={[
                    styles.toggleText,
                    form.show_on_cover && styles.toggleTextActive,
                  ]}>
                  {form.show_on_cover ? 'Sí' : 'No'}
                </Text>
              </TouchableOpacity>
            </View>

            {form.show_on_cover ? (
              <>
                <Text style={styles.label}>Título público</Text>
                <TextInput
                  style={styles.input}
                  value={form.cover_title ?? ''}
                  onChangeText={text =>
                    setForm(prev => ({...prev, cover_title: text}))
                  }
                  placeholder="Ej. Favoritos de la casa"
                  placeholderTextColor="#64748b"
                />

                <Text style={styles.label}>Subtítulo (opcional)</Text>
                <TextInput
                  style={styles.input}
                  value={form.cover_subtitle ?? ''}
                  onChangeText={text =>
                    setForm(prev => ({...prev, cover_subtitle: text}))
                  }
                  placeholder="Texto corto para la portada"
                  placeholderTextColor="#64748b"
                />
              </>
            ) : null}

            {error ? <Text style={styles.error}>{error}</Text> : null}
          </ScrollView>

          <View style={styles.actions}>
            <TouchableOpacity
              style={styles.cancelButton}
              disabled={submitting || loading}
              onPress={onClose}>
              <Text style={styles.cancelText}>Cancelar</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.saveButton}
              disabled={submitting || loading}
              onPress={handleSubmit}>
              {submitting || loading ? (
                <ActivityIndicator color="#0f172a" />
              ) : (
                <Text style={styles.saveText}>Guardar</Text>
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
    backgroundColor: 'rgba(0,0,0,0.7)',
    justifyContent: 'center',
    padding: 16,
  },
  modal: {
    backgroundColor: '#0f172a',
    borderRadius: 28,
    padding: 20,
    borderWidth: 1,
    borderColor: '#1e293b',
    maxHeight: '90%',
  },
  title: {
    color: '#f8fafc',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 16,
  },
  label: {
    color: '#cbd5f5',
    marginBottom: 6,
    marginTop: 12,
    fontWeight: '600',
  },
  input: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    padding: 12,
    color: '#f8fafc',
    backgroundColor: '#0b1120',
  },
  toggleRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 16,
  },
  togglePill: {
    borderRadius: 999,
    paddingHorizontal: 18,
    paddingVertical: 6,
    borderWidth: 1,
    borderColor: '#1e293b',
  },
  togglePillActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  toggleText: {
    color: '#94a3b8',
    fontWeight: '600',
  },
  toggleTextActive: {
    color: '#0f172a',
  },
  actions: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    marginTop: 20,
    gap: 12,
  },
  cancelButton: {
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#475569',
  },
  cancelText: {
    color: '#94a3b8',
    fontWeight: '600',
  },
  saveButton: {
    paddingHorizontal: 24,
    paddingVertical: 10,
    borderRadius: 999,
    backgroundColor: '#fbbf24',
  },
  saveText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  error: {
    color: '#f87171',
    marginTop: 12,
  },
});

export default CategoryFormModal;
