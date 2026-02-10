import axios from 'axios';
import {NativeModules, Platform} from 'react-native';
import {
  Campaign,
  CategoryFormInput,
  CategoryPayload,
  DishFormInput,
  DiningTable,
  ExtraOption,
  LoginResponse,
  ManagerSummary,
  ManagerOpsSummary,
  ManagerMenuView,
  ManagerView,
  PosTicket,
  PosTicketPayload,
  PrepArea,
  PrepLabel,
  KitchenOrder,
  TipSettings,
  WaitingListEntry,
  WaitingListSettings,
  TapToPayConfig,
  ServerUser,
  ServerDashboardSummary,
  SummaryResponse,
  Tax,
  ServerOrderItemPayload,
  TableSession,
  TableSessionPayload,
  VisitPayload,
  ViewSettingsResponse,
} from '../types';

const PROD_API_BASE_URL = 'https://madeleinecafebistro.com/api/mobile';
// For dev builds use local API; production builds will still use PROD_API_BASE_URL.
const FORCE_PROD_API = false;

const resolveDevHost = () => {
  const scriptURL = NativeModules.SourceCode?.scriptURL as string | undefined;
  if (scriptURL) {
    const match = scriptURL.match(/https?:\/\/([^:/]+)(?::\d+)?/);
    if (match?.[1]) {
      return match[1];
    }
  }
  return Platform.OS === 'android' ? '10.0.2.2' : '127.0.0.1';
};

const DEV_API_BASE_URL = `http://${resolveDevHost()}:8002/api/mobile`;

export const API_BASE_URL = FORCE_PROD_API
  ? PROD_API_BASE_URL
  : __DEV__
    ? DEV_API_BASE_URL
    : PROD_API_BASE_URL;

export const WEB_BASE_URL = API_BASE_URL.replace(/\/api\/mobile$/, '');

const SERVER_API = `${API_BASE_URL}/servers`;
const MANAGER_API = `${API_BASE_URL}/managers`;
const HOST_API = `${API_BASE_URL}/hosts`;
const POS_API = `${API_BASE_URL}/pos`;
const KITCHEN_API = `${API_BASE_URL}/kitchen`;

const client = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000,
});

const authHeaders = (token: string) => ({
  headers: {
    Authorization: `Bearer ${token}`,
  },
});

const extractMessage = (error: unknown) => {
  if (axios.isAxiosError(error)) {
    const validationErrors = error.response?.data?.errors as
      | Record<string, string[]>
      | undefined;
    const fallbackError = validationErrors
      ? Object.values(validationErrors)[0]?.[0]
      : undefined;
    const message = error.response?.data?.message || fallbackError;
    if (message) {
      return message as string;
    }
  }
  return 'Ocurrió un error inesperado.';
};

export async function login(
  email: string,
  password: string,
): Promise<LoginResponse> {
  try {
    const {data} = await client.post<LoginResponse>('/login', {
      email,
      password,
    });
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function logout(token: string) {
  try {
    await client.post('/logout', null, authHeaders(token));
  } catch {
    // ignoramos errores de logout para evitar bloquear al usuario
  }
}

export async function getServerSummary(
  token: string,
): Promise<SummaryResponse> {
  try {
    const {data} = await client.get<SummaryResponse>(
      `${SERVER_API}/visits/summary`,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getTipSettings(token: string): Promise<TipSettings> {
  try {
    const {data} = await client.get<TipSettings>(
      `${API_BASE_URL}/settings/tips`,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getMobileViewSettings(
  token: string,
): Promise<ViewSettingsResponse> {
  try {
    const {data} = await client.get<ViewSettingsResponse>(
      `${API_BASE_URL}/settings/views`,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getTapToPayConfig(token: string): Promise<TapToPayConfig> {
  try {
    const {data} = await client.get<TapToPayConfig>(
      `${API_BASE_URL}/tap-to-pay/config`,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getServerDashboardSummary(
  token: string,
): Promise<ServerDashboardSummary> {
  try {
    const {data} = await client.get<{summary: ServerDashboardSummary}>(
      `${SERVER_API}/dashboard`,
      authHeaders(token),
    );
    return data.summary;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}


export async function getServerDiningTables(
  token: string,
  params?: {status?: string; section?: string},
): Promise<DiningTable[]> {
  try {
    const {data} = await client.get<{tables: DiningTable[]}>(
      `${SERVER_API}/tables`,
      {
        ...authHeaders(token),
        params,
      },
    );
    return data.tables ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getAvailableServers(
  token: string,
  scope: 'manager' | 'host' = 'manager',
): Promise<ServerUser[]> {
  try {
    const base = scope === 'host' ? HOST_API : SERVER_API;
    const {data} = await client.get<{servers: ServerUser[]}>(
      `${base}/servers/available`,
      authHeaders(token),
    );
    return data.servers ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createVisit(
  token: string,
  payload: VisitPayload,
): Promise<void> {
  try {
    await client.post(`${SERVER_API}/visits`, payload, authHeaders(token));
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createTableSession(
  token: string,
  payload: TableSessionPayload,
): Promise<TableSession> {
  try {
    const {data} = await client.post<{session: TableSession}>(
      `${SERVER_API}/table-sessions`,
      payload,
      authHeaders(token),
    );
    return data.session;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getServerMenuCategories(
  token: string,
  view: ManagerView,
): Promise<CategoryPayload[]> {
  try {
    const endpoint =
      view === 'menu'
        ? 'menu'
        : view === 'cocktails'
          ? 'cocktails'
          : view === 'wines'
            ? 'wines'
            : 'cantina';
    const {data} = await client.get<{categories: CategoryPayload[]}>(
      `${SERVER_API}/${endpoint}/categories`,
      authHeaders(token),
    );
    return data.categories ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createServerOrder(
  token: string,
  sessionId: number,
  items: ServerOrderItemPayload[],
): Promise<{order_id: number; batch_id: number}> {
  try {
    const {data} = await client.post<{order_id: number; batch_id: number}>(
      `${SERVER_API}/table-sessions/${sessionId}/orders`,
      {items},
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getPosTickets(token: string): Promise<PosTicket[]> {
  try {
    const {data} = await client.get<{tickets: PosTicket[]}>(
      `${POS_API}/tickets`,
      authHeaders(token),
    );
    return data.tickets ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createPosTicket(
  token: string,
  payload: PosTicketPayload,
): Promise<PosTicket> {
  try {
    const {data} = await client.post<{ticket: PosTicket}>(
      `${POS_API}/tickets`,
      payload,
      authHeaders(token),
    );
    return data.ticket;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getPosTicket(
  token: string,
  ticketId: number,
): Promise<PosTicket> {
  try {
    const {data} = await client.get<{ticket: PosTicket}>(
      `${POS_API}/tickets/${ticketId}`,
      authHeaders(token),
    );
    return data.ticket;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createPosBatch(
  token: string,
  ticketId: number,
  items: ServerOrderItemPayload[],
): Promise<void> {
  try {
    await client.post(
      `${POS_API}/tickets/${ticketId}/batches`,
      {items},
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function confirmPosBatch(
  token: string,
  batchId: number,
): Promise<void> {
  try {
    await client.patch(
      `${POS_API}/batches/${batchId}/confirm`,
      {},
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function cancelPosBatch(
  token: string,
  batchId: number,
): Promise<void> {
  try {
    await client.patch(
      `${POS_API}/batches/${batchId}/cancel`,
      {},
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function payPosTicket(
  token: string,
  ticketId: number,
  method: 'cash' | 'card' | 'ath' | 'split' | 'tap_to_pay',
  tip?: number,
): Promise<{total: number}> {
  try {
    const {data} = await client.patch<{total: number}>(
      `${POS_API}/tickets/${ticketId}/pay`,
      {method, tip},
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function sendPosReceipt(
  token: string,
  ticketId: number,
): Promise<{message?: string; receipt_url?: string}> {
  try {
    const {data} = await client.post<{message?: string; receipt_url?: string}>(
      `${POS_API}/tickets/${ticketId}/receipt`,
      {},
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}


export async function createPosPayment(
  token: string,
  ticketId: number,
  payload: {
    method: 'cash' | 'card' | 'ath' | 'split';
    split_mode: 'items' | 'amount';
    items?: number[];
    amount?: number;
    tip?: number;
    tip_percent?: number;
  },
): Promise<{
  summary: {
    subtotal: number;
    paid_subtotal: number;
    tip_total: number;
    balance: number;
    is_paid: boolean;
  };
}> {
  try {
    const {data} = await client.post<{summary: {subtotal: number; paid_subtotal: number; tip_total: number; balance: number; is_paid: boolean}}>(
      `${POS_API}/tickets/${ticketId}/payments`,
      payload,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createPosTerminalConnectionToken(
  token: string,
): Promise<string> {
  try {
    const {data} = await client.post<{secret: string | null}>(
      `${POS_API}/terminal/connection-token`,
      {},
      authHeaders(token),
    );
    if (!data.secret) {
      throw new Error('No se pudo iniciar el lector.');
    }
    return data.secret;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createServerTerminalConnectionToken(
  token: string,
): Promise<string> {
  try {
    const {data} = await client.post<{secret: string | null}>(
      `${SERVER_API}/terminal/connection-token`,
      {},
      authHeaders(token),
    );
    if (!data.secret) {
      throw new Error('No se pudo iniciar el lector.');
    }
    return data.secret;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createPosTerminalPaymentIntent(
  token: string,
  orderId: number,
  tip?: number,
): Promise<{
  client_secret: string;
  payment_intent_id: string | null;
  amount: number;
  currency: string;
}> {
  try {
    const idempotencyKey = `${orderId}-${Date.now()}-${Math.floor(Math.random() * 100000)}`;
    const {data} = await client.post<{
      client_secret: string;
      payment_intent_id: string | null;
      amount: number;
      currency: string;
    }>(
      `${POS_API}/terminal/payment-intents`,
      {order_id: orderId, tip},
      {
        ...authHeaders(token),
        headers: {
          ...authHeaders(token).headers,
          'Idempotency-Key': idempotencyKey,
        },
      },
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createServerTerminalPaymentIntent(
  token: string,
  orderId: number,
  tip?: number,
): Promise<{
  client_secret: string;
  payment_intent_id: string | null;
  amount: number;
  currency: string;
}> {
  try {
    const idempotencyKey = `${orderId}-${Date.now()}-${Math.floor(Math.random() * 100000)}`;
    const {data} = await client.post<{
      client_secret: string;
      payment_intent_id: string | null;
      amount: number;
      currency: string;
    }>(
      `${SERVER_API}/terminal/payment-intents`,
      {order_id: orderId, tip},
      {
        ...authHeaders(token),
        headers: {
          ...authHeaders(token).headers,
          'Idempotency-Key': idempotencyKey,
        },
      },
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function payServerTableSession(
  token: string,
  sessionId: number,
  method: 'cash' | 'card' | 'ath' | 'split' | 'tap_to_pay',
  tip?: number,
): Promise<void> {
  try {
    await client.patch(
      `${SERVER_API}/table-sessions/${sessionId}/pay`,
      {method, tip},
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function confirmServerExternalPayment(
  token: string,
  sessionId: number,
  payload: {
    method: 'cash' | 'card' | 'ath' | 'tap_to_pay';
    provider: string;
    payload: Record<string, unknown>;
    tip?: number;
    amount?: number;
  },
): Promise<{
  summary?: {
    subtotal: number;
    paid_subtotal: number;
    tip_total: number;
    balance: number;
    is_paid: boolean;
  };
  receipt_url?: string | null;
}> {
  try {
    const {data} = await client.post<{
      summary?: {
        subtotal: number;
        paid_subtotal: number;
        tip_total: number;
        balance: number;
        is_paid: boolean;
      };
      receipt_url?: string | null;
    }>(
      `${SERVER_API}/table-sessions/${sessionId}/payments/external`,
      payload,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function sendServerReceipt(
  token: string,
  sessionId: number,
): Promise<{message?: string; receipt_url?: string}> {
  try {
    const {data} = await client.post<{message?: string; receipt_url?: string}>(
      `${SERVER_API}/table-sessions/${sessionId}/receipt`,
      {},
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}


export async function transferServerTableSession(
  token: string,
  sessionId: number,
  payload: {
    table_label: string;
    server_id?: number;
    manager_email?: string;
    manager_password?: string;
    reason?: string;
  },
): Promise<{message?: string; session?: TableSession}> {
  try {
    const {data} = await client.patch<{message?: string; session?: TableSession}>(
      `${SERVER_API}/table-sessions/${sessionId}/transfer`,
      payload,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createServerPayment(
  token: string,
  sessionId: number,
  payload: {
    method: 'cash' | 'card' | 'ath' | 'split';
    split_mode: 'items' | 'amount';
    items?: number[];
    amount?: number;
    tip?: number;
    tip_percent?: number;
  },
): Promise<{
  summary: {
    subtotal: number;
    paid_subtotal: number;
    tip_total: number;
    balance: number;
    is_paid: boolean;
  };
}> {
  try {
    const {data} = await client.post<{summary: {subtotal: number; paid_subtotal: number; tip_total: number; balance: number; is_paid: boolean}}>(
      `${SERVER_API}/table-sessions/${sessionId}/payments`,
      payload,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function confirmPosExternalPayment(
  token: string,
  ticketId: number,
  payload: {
    method: 'cash' | 'card' | 'ath' | 'tap_to_pay';
    provider: string;
    payload: Record<string, unknown>;
    tip?: number;
    amount?: number;
  },
): Promise<{
  summary?: {
    subtotal: number;
    paid_subtotal: number;
    tip_total: number;
    balance: number;
    is_paid: boolean;
  };
  receipt_url?: string | null;
}> {
  try {
    const {data} = await client.post<{
      summary?: {
        subtotal: number;
        paid_subtotal: number;
        tip_total: number;
        balance: number;
        is_paid: boolean;
      };
      receipt_url?: string | null;
    }>(
      `${POS_API}/tickets/${ticketId}/payments/external`,
      payload,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}
export async function voidPosOrderItem(
  token: string,
  batchId: number,
  itemId: number,
  reason?: string,
) {
  try {
    await client.patch(
      `${POS_API}/batches/${batchId}/items/${itemId}/void`,
      {reason},
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function overridePosOrderItem(
  token: string,
  batchId: number,
  itemId: number,
  managerEmail: string,
  managerPassword: string,
  reason?: string,
) {
  try {
    await client.patch(
      `${POS_API}/batches/${batchId}/items/${itemId}/override`,
      {
        manager_email: managerEmail,
        manager_password: managerPassword,
        reason,
      },
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getPosMenuCategories(
  token: string,
  view: ManagerView,
): Promise<CategoryPayload[]> {
  try {
    const endpoint =
      view === 'menu'
        ? 'menu'
        : view === 'cocktails'
          ? 'cocktails'
          : view === 'wines'
            ? 'wines'
            : 'cantina';
    const {data} = await client.get<{categories: CategoryPayload[]}>(
      `${POS_API}/${endpoint}/categories`,
      authHeaders(token),
    );
    return data.categories ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getActiveTableSessions(
  token: string,
): Promise<TableSession[]> {
  try {
    const {data} = await client.get<{sessions: TableSession[]}>(
      `${SERVER_API}/table-sessions/active`,
      authHeaders(token),
    );
    return data.sessions ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function renewTableSession(token: string, sessionId: number) {
  try {
    await client.patch(
      `${SERVER_API}/table-sessions/${sessionId}/renew`,
      null,
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function closeTableSession(
  token: string,
  sessionId: number,
  tip?: number,
) {
  try {
    await client.patch(
      `${SERVER_API}/table-sessions/${sessionId}/close`,
      {tip},
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function updateTableSessionOrderMode(
  token: string,
  sessionId: number,
  orderMode: 'traditional' | 'table',
): Promise<TableSession> {
  try {
    const {data} = await client.patch<{session: TableSession}>(
      `${SERVER_API}/table-sessions/${sessionId}/order-mode`,
      {order_mode: orderMode},
      authHeaders(token),
    );
    return data.session;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function confirmOrder(token: string, orderId: number) {
  try {
    await client.patch(
      `${SERVER_API}/orders/${orderId}/confirm`,
      null,
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function cancelOrder(token: string, orderId: number) {
  try {
    await client.patch(
      `${SERVER_API}/orders/${orderId}/cancel`,
      null,
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getKitchenAreas(token: string): Promise<PrepArea[]> {
  try {
    const {data} = await client.get<{areas: PrepArea[]}>(
      `${KITCHEN_API}/areas`,
      authHeaders(token),
    );
    return data.areas ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getKitchenOrders(
  token: string,
  params: {areaId?: number; labelId?: number; status?: string},
): Promise<KitchenOrder[]> {
  try {
    const {data} = await client.get<{orders: KitchenOrder[]}>(
      `${KITCHEN_API}/orders`,
      {
        ...authHeaders(token),
        params: {
          area_id: params.areaId,
          label_id: params.labelId,
          status: params.status,
        },
      },
    );
    return data.orders ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function updateKitchenOrderItemStatus(
  token: string,
  orderItemId: number,
  labelId: number,
  status: 'pending' | 'preparing' | 'ready' | 'delivered' | 'cancelled',
): Promise<void> {
  try {
    await client.patch(
      `${KITCHEN_API}/order-items/${orderItemId}`,
      {label_id: labelId, status},
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function voidServerOrderItem(
  token: string,
  orderId: number,
  itemId: number,
  reason?: string,
) {
  try {
    await client.patch(
      `${SERVER_API}/orders/${orderId}/items/${itemId}/void`,
      {reason},
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function overrideServerOrderItem(
  token: string,
  orderId: number,
  itemId: number,
  managerEmail: string,
  managerPassword: string,
  reason?: string,
) {
  try {
    await client.patch(
      `${SERVER_API}/orders/${orderId}/items/${itemId}/override`,
      {
        manager_email: managerEmail,
        manager_password: managerPassword,
        reason,
      },
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}


export async function getDiningTables(
  token: string,
  params?: {status?: string; section?: string},
  scope: 'manager' | 'host' = 'manager',
): Promise<DiningTable[]> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.get<{tables: DiningTable[]}>(
      `${base}/tables`,
      {
        ...authHeaders(token),
        params,
      },
    );
    return data.tables ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createDiningTable(
  token: string,
  payload: Partial<DiningTable> & {label: string; capacity: number},
  scope: 'manager' | 'host' = 'manager',
): Promise<DiningTable> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.post<{table: DiningTable}>(
      `${base}/tables`,
      payload,
      authHeaders(token),
    );
    return data.table;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function updateDiningTable(
  token: string,
  tableId: number,
  payload: Partial<DiningTable>,
  scope: 'manager' | 'host' = 'manager',
): Promise<DiningTable> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.put<{table: DiningTable}>(
      `${base}/tables/${tableId}`,
      payload,
      authHeaders(token),
    );
    return data.table;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function updateDiningTableStatus(
  token: string,
  tableId: number,
  status: DiningTable['status'],
  scope: 'manager' | 'host' = 'manager',
): Promise<DiningTable> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.patch<{table: DiningTable}>(
      `${base}/tables/${tableId}/status`,
      {status},
      authHeaders(token),
    );
    return data.table;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function deleteDiningTable(
  token: string,
  tableId: number,
  scope: 'manager' | 'host' = 'manager',
) {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    await client.delete(`${base}/tables/${tableId}`, authHeaders(token));
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getWaitingList(
  token: string,
  params?: {status?: string},
  scope: 'manager' | 'host' = 'manager',
): Promise<WaitingListEntry[]> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.get<{entries: WaitingListEntry[]}>(
      `${base}/waiting-list`,
      {
        ...authHeaders(token),
        params,
      },
    );
    return data.entries ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createWaitingListEntry(
  token: string,
  payload: {
    guest_name: string;
    guest_phone: string;
    guest_email?: string | null;
    party_size: number;
    notes?: string | null;
    quoted_minutes?: number | null;
    reservation_at?: string | null;
  },
  scope: 'manager' | 'host' = 'manager',
): Promise<WaitingListEntry> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.post<{entry: WaitingListEntry}>(
      `${base}/waiting-list`,
      payload,
      authHeaders(token),
    );
    return data.entry;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function updateWaitingListEntry(
  token: string,
  entryId: number,
  payload: Partial<WaitingListEntry>,
  scope: 'manager' | 'host' = 'manager',
): Promise<WaitingListEntry> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.patch<{entry: WaitingListEntry}>(
      `${base}/waiting-list/${entryId}`,
      payload,
      authHeaders(token),
    );
    return data.entry;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function notifyWaitingListEntry(
  token: string,
  entryId: number,
  scope: 'manager' | 'host' = 'manager',
): Promise<WaitingListEntry> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.post<{entry: WaitingListEntry}>(
      `${base}/waiting-list/${entryId}/notify`,
      null,
      authHeaders(token),
    );
    return data.entry;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function assignWaitingListTables(
  token: string,
  entryId: number,
  payload: {
    table_ids: number[];
    mode?: 'reserve' | 'seat';
    replace?: boolean;
    server_id?: number | null;
  },
  scope: 'manager' | 'host' = 'manager',
): Promise<WaitingListEntry> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.post<{entry: WaitingListEntry}>(
      `${base}/waiting-list/${entryId}/assign`,
      payload,
      authHeaders(token),
    );
    return data.entry;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getWaitingListSettings(
  token: string,
  scope: 'manager' | 'host' = 'manager',
): Promise<WaitingListSettings> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.get<{settings: WaitingListSettings}>(
      `${base}/waiting-list/settings`,
      authHeaders(token),
    );
    return data.settings;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function updateWaitingListSettings(
  token: string,
  payload: Partial<WaitingListSettings>,
  scope: 'manager' | 'host' = 'manager',
): Promise<WaitingListSettings> {
  try {
    const base = scope === 'host' ? HOST_API : MANAGER_API;
    const {data} = await client.patch<{settings: WaitingListSettings}>(
      `${base}/waiting-list/settings`,
      payload,
      authHeaders(token),
    );
    return data.settings;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getManagerDashboard(
  token: string,
): Promise<ManagerSummary> {
  try {
    const {data} = await client.get<ManagerSummary>(
      `${MANAGER_API}/dashboard`,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getManagerOperations(
  token: string,
): Promise<ManagerOpsSummary> {
  try {
    const {data} = await client.get<ManagerOpsSummary>(
      `${MANAGER_API}/operations`,
      authHeaders(token),
    );
    return data;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getManagerServers(token: string) {
  try {
    const {data} = await client.get<{servers: ServerUser[]}>(
      `${MANAGER_API}/servers`,
      authHeaders(token),
    );
    return data.servers;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function toggleServer(token: string, serverId: number) {
  try {
    await client.patch(
      `${MANAGER_API}/servers/${serverId}/toggle`,
      null,
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getMenuCategories(
  token: string,
): Promise<CategoryPayload[]> {
  return getManagedCategories(token, 'menu');
}

type ManagedEndpoints = {
  basePath: string;
  itemPath: string;
};

const VIEW_ENDPOINTS: Record<ManagerMenuView, ManagedEndpoints> = {
  menu: {basePath: 'menu', itemPath: 'dishes'},
  cocktails: {basePath: 'cocktails', itemPath: 'items'},
  wines: {basePath: 'wines', itemPath: 'items'},
  cantina: {basePath: 'cantina', itemPath: 'items'},
};

const viewUrl = (view: ManagerMenuView, suffix: string) =>
  `${MANAGER_API}/${VIEW_ENDPOINTS[view].basePath}${suffix}`;

export async function getManagedCategories(
  token: string,
  view: ManagerMenuView,
): Promise<CategoryPayload[]> {
  try {
    const {data} = await client.get<{categories: CategoryPayload[]}>(
      viewUrl(view, '/categories'),
      authHeaders(token),
    );
    return data.categories ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function createCategory(
  token: string,
  view: ManagerMenuView,
  payload: CategoryFormInput,
): Promise<CategoryPayload> {
  try {
    const {data} = await client.post<{category: CategoryPayload}>(
      viewUrl(view, '/categories'),
      payload,
      authHeaders(token),
    );
    return data.category;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function updateCategory(
  token: string,
  view: ManagerMenuView,
  categoryId: number,
  payload: CategoryFormInput,
): Promise<CategoryPayload> {
  try {
    const {data} = await client.put<{category: CategoryPayload}>(
      viewUrl(view, `/categories/${categoryId}`),
      payload,
      authHeaders(token),
    );
    return data.category;
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function deleteCategory(
  token: string,
  view: ManagerMenuView,
  categoryId: number,
): Promise<void> {
  try {
    await client.delete(viewUrl(view, `/categories/${categoryId}`), authHeaders(token));
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function reorderCategories(
  token: string,
  view: ManagerMenuView,
  order: number[],
): Promise<void> {
  try {
    await client.post(
      viewUrl(view, '/categories/reorder'),
      {order},
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getCampaigns(token: string): Promise<Campaign[]> {
  try {
    const {data} = await client.get<{campaigns: Campaign[]}>(
      `${MANAGER_API}/campaigns`,
      authHeaders(token),
    );
    return data.campaigns ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getManagerExtras(
  token: string,
  viewScope?: string,
  activeOnly = true,
): Promise<ExtraOption[]> {
  try {
    const params: Record<string, string> = {};
    if (viewScope) {
      params.view_scope = viewScope;
    }
    if (activeOnly) {
      params.active = '1';
    }
    const {data} = await client.get<ExtraOption[]>(
      `${MANAGER_API}/extras`,
      {
        ...authHeaders(token),
        params,
      },
    );
    return data ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getManagerPrepLabels(
  token: string,
): Promise<PrepLabel[]> {
  try {
    const {data} = await client.get<{labels: PrepLabel[]}>(
      `${MANAGER_API}/prep-labels`,
      authHeaders(token),
    );
    return data.labels ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function getManagerTaxes(token: string): Promise<Tax[]> {
  try {
    const {data} = await client.get<{taxes: Tax[]}>(
      `${MANAGER_API}/taxes`,
      authHeaders(token),
    );
    return data.taxes ?? [];
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

type ImagePayload = {
  uri: string;
  type?: string;
  name?: string;
};

const appendOptionalBoolean = (
  formData: FormData,
  name: string,
  value?: boolean,
) => {
  if (value !== undefined) {
    formData.append(name, value ? '1' : '0');
  }
};

const appendArrayField = (
  formData: FormData,
  name: string,
  values?: number[],
) => {
  if (values && values.length) {
    values.forEach(value => formData.append(`${name}[]`, String(value)));
  }
};

const buildItemFormData = (payload: DishFormInput, image?: ImagePayload) => {
  const formData = new FormData();
  formData.append('name', payload.name);
  formData.append('description', payload.description);
  formData.append('price', payload.price);
  formData.append('category_id', String(payload.category_id));
  appendOptionalBoolean(
    formData,
    'visible',
    payload.visible === undefined ? true : payload.visible,
  );
  appendOptionalBoolean(formData, 'featured_on_cover', payload.featured_on_cover);

  if (payload.type_id !== undefined && payload.type_id !== null) {
    formData.append('type_id', String(payload.type_id));
  }
  if (payload.region_id !== undefined && payload.region_id !== null) {
    formData.append('region_id', String(payload.region_id));
  }

  appendArrayField(formData, 'grapes', payload.grapes);
  appendArrayField(formData, 'food_pairings', payload.food_pairings);
  appendArrayField(formData, 'recommended_dishes', payload.recommended_dishes);
  appendArrayField(formData, 'extra_ids', payload.extra_ids);
  appendArrayField(formData, 'prep_label_ids', payload.prep_label_ids);
  appendArrayField(formData, 'tax_ids', payload.tax_ids);

  if (image) {
    formData.append('image', {
      uri: image.uri,
      name: image.name ?? `dish-${Date.now()}.jpg`,
      type: image.type ?? 'image/jpeg',
    } as any);
  }
  return formData;
};

const multipartHeaders = (token: string) => ({
  headers: {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'multipart/form-data',
  },
});

export async function createManagedItem(
  token: string,
  view: ManagerMenuView,
  payload: DishFormInput,
  image?: ImagePayload,
) {
  try {
    const data = buildItemFormData(payload, image);
    await client.post(viewUrl(view, `/${VIEW_ENDPOINTS[view].itemPath}`), data, multipartHeaders(token));
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function updateManagedItem(
  token: string,
  view: ManagerMenuView,
  itemId: number,
  payload: DishFormInput,
  image?: ImagePayload,
) {
  try {
    const data = buildItemFormData(payload, image);
    const url = `${viewUrl(view, `/${VIEW_ENDPOINTS[view].itemPath}/${itemId}`)}?_method=PUT`;
    await client.post(url, data, multipartHeaders(token));
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function deleteManagedItem(
  token: string,
  view: ManagerMenuView,
  itemId: number,
) {
  try {
    await client.delete(
      viewUrl(view, `/${VIEW_ENDPOINTS[view].itemPath}/${itemId}`),
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

export async function toggleManagedItem(
  token: string,
  view: ManagerMenuView,
  itemId: number,
) {
  try {
    await client.patch(
      viewUrl(view, `/${VIEW_ENDPOINTS[view].itemPath}/${itemId}/toggle`),
      null,
      authHeaders(token),
    );
  } catch (error) {
    throw new Error(extractMessage(error));
  }
}

// Legacy wrappers kept for compatibility with other modules.
export const createDish = (
  token: string,
  payload: DishFormInput,
  image?: ImagePayload,
) => createManagedItem(token, 'menu', payload, image);

export const updateDish = (
  token: string,
  dishId: number,
  payload: DishFormInput,
  image?: ImagePayload,
) => updateManagedItem(token, 'menu', dishId, payload, image);

export const deleteDish = (token: string, dishId: number) =>
  deleteManagedItem(token, 'menu', dishId);

export const toggleDishVisibility = (token: string, dishId: number) =>
  toggleManagedItem(token, 'menu', dishId);
