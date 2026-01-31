import {TableOrder, TableOrderItem, TableOrderItemLabel} from '../types';

const scopeLabelMap: Record<string, string> = {
  cocktails: 'Cócteles',
  wines: 'Bebidas',
  menu: 'Menú',
};

const scopeOrderMap: Record<string, number> = {
  menu: 0,
  cocktails: 1,
  wines: 2,
};

export const scopeLabel = (scope?: string | null) =>
  scope ? scopeLabelMap[scope] ?? 'Otros' : 'Otros';

const scopeOrder = (scope?: string | null) =>
  scope ? scopeOrderMap[scope] ?? 3 : 3;

type CategoryGroup = {
  scope: string | null;
  categoryName: string;
  categoryOrder: number;
  items: TableOrderItem[];
};

export const groupItemsByCategory = (items: TableOrderItem[]) => {
  const grouped = new Map<string, CategoryGroup>();

  items.forEach(item => {
    const scope = item.category_scope ?? 'menu';
    const categoryKey = `${scope}-${item.category_id ?? item.category_name ?? 'unknown'}`;
    if (!grouped.has(categoryKey)) {
      grouped.set(categoryKey, {
        scope,
        categoryName: item.category_name || 'Sin categoría',
        categoryOrder: item.category_order ?? 0,
        items: [],
      });
    }
    grouped.get(categoryKey)?.items.push(item);
  });

  return Array.from(grouped.values()).sort((a, b) => {
    const scopeDiff = scopeOrder(a.scope) - scopeOrder(b.scope);
    if (scopeDiff !== 0) {
      return scopeDiff;
    }
    if (a.categoryOrder !== b.categoryOrder) {
      return a.categoryOrder - b.categoryOrder;
    }
    return a.categoryName.localeCompare(b.categoryName);
  });
};

export const formatTime = (value?: string | null) => {
  if (!value) {
    return null;
  }
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return null;
  }
  return date.toLocaleTimeString('es-PR', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

export const formatElapsed = (
  start?: string | null,
  end?: string | null,
) => {
  if (!start) {
    return null;
  }
  const startDate = new Date(start);
  if (Number.isNaN(startDate.getTime())) {
    return null;
  }
  const endDate = end ? new Date(end) : new Date();
  if (Number.isNaN(endDate.getTime())) {
    return null;
  }
  const diffMs = Math.max(0, endDate.getTime() - startDate.getTime());
  const minutes = Math.floor(diffMs / 60000);
  if (minutes < 1) {
    return 'Hace <1 min';
  }
  if (minutes < 60) {
    return `Hace ${minutes} min`;
  }
  const hours = Math.floor(minutes / 60);
  const remaining = minutes % 60;
  return `Hace ${hours}h ${remaining}m`;
};

export const formatDuration = (
  start?: string | null,
  end?: string | null,
) => {
  if (!start) {
    return null;
  }
  const startDate = new Date(start);
  if (Number.isNaN(startDate.getTime())) {
    return null;
  }
  const endDate = end ? new Date(end) : new Date();
  if (Number.isNaN(endDate.getTime())) {
    return null;
  }
  const diffMs = Math.max(0, endDate.getTime() - startDate.getTime());
  const minutes = Math.floor(diffMs / 60000);
  if (minutes < 1) {
    return '<1 min';
  }
  if (minutes < 60) {
    return `${minutes} min`;
  }
  const hours = Math.floor(minutes / 60);
  const remaining = minutes % 60;
  return `${hours}h ${remaining}m`;
};

export const labelStatusLabel = (status?: string | null) => {
  switch (status) {
    case 'preparing':
      return 'Preparando';
    case 'ready':
      return 'Listo';
    case 'delivered':
      return 'Entregado';
    case 'cancelled':
      return 'Cancelado';
    default:
      return 'Pendiente';
  }
};

const getLabelPriority = (label: TableOrderItemLabel) => {
  switch (label.status) {
    case 'ready':
      return 3;
    case 'preparing':
      return 2;
    case 'pending':
      return 1;
    case 'delivered':
      return 0;
    default:
      return 1;
  }
};

const pickTimestamp = (timestamps: Array<string | null | undefined>, order: 'asc' | 'desc') => {
  const normalized = timestamps
    .filter(Boolean)
    .map(value => new Date(value as string))
    .filter(date => !Number.isNaN(date.getTime()));

  if (!normalized.length) {
    return null;
  }

  normalized.sort((a, b) =>
    order === 'asc' ? a.getTime() - b.getTime() : b.getTime() - a.getTime(),
  );

  return normalized[0].toISOString();
};

export const getKitchenSummary = (order: TableOrder) => {
  const labels = (order.items ?? []).flatMap(item => item.labels ?? []);
  if (!labels.length) {
    return null;
  }

  const allDelivered = labels.every(label => label.status === 'delivered');
  let status: TableOrderItemLabel['status'] = 'pending';

  if (allDelivered) {
    status = 'delivered';
  } else {
    status = labels.reduce((best, current) => {
      return getLabelPriority(current) > getLabelPriority(best) ? current : best;
    }, labels[0]).status;
  }

  const preparedAt = pickTimestamp(
    labels.map(label => label.prepared_at),
    'asc',
  );
  const startAt = preparedAt ?? order.created_at ?? null;
  const endAt = allDelivered
    ? pickTimestamp(labels.map(label => label.delivered_at), 'desc')
    : null;

  return {status, startAt, endAt};
};

export const timeLeft = (value?: string | null) => {
  if (!value) {
    return null;
  }
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return null;
  }
  const diffMs = date.getTime() - Date.now();
  const minutes = Math.max(0, Math.round(diffMs / 60000));
  return minutes;
};
