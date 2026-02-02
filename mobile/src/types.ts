export type User = {
  id: number;
  name: string;
  email: string;
  role: 'manager' | 'server' | 'pos' | 'kitchen' | 'host' | string;
  active?: boolean;
};

export type RecentVisit = {
  id: number;
  name: string;
  email: string;
  phone: string;
  status: string;
  points: number;
  created_at: string | null;
};

export type SummaryResponse = {
  points_per_visit: number;
  qr_url: string | null;
  active_visit: RecentVisit | null;
  recent_visits: RecentVisit[];
  terms?: string | null;
  rewards?: LoyaltyRewardSummary[];
};

export type TipSettings = {
  presets: number[];
  allow_custom: boolean;
  allow_skip: boolean;
};

export type TapToPayConfig = {
  tpn: string;
  merchant_code: string;
  auth_token?: string | null;
  environment?: 'UAT' | 'PROD' | string;
};

export type LoyaltyRewardSummary = {
  id: number;
  title: string;
  description: string | null;
  points_required: number;
};

export type ManagerTotals = {
  total_visits: number;
  pending_visits: number;
  confirmed_visits: number;
  points_distributed: number;
};

export type ManagerSummary = {
  totals: ManagerTotals;
  daily_visits: {day: string; visits: number}[];
};

export type ManagerOpsTotals = {
  sales_total: number;
  tips_total: number;
  orders_count: number;
  open_tables: number;
  open_tickets: number;
  voided_total: number;
  sales_total_yesterday: number;
  sales_delta_percent: number | null;
  sales_week_total: number;
  sales_week_prev: number;
  sales_week_delta_percent: number | null;
};

export type ManagerOpsChannel = {
  channel: string;
  sales_total: number;
  orders_count: number;
};

export type ManagerOpsTopItem = {
  name: string;
  quantity: number;
  revenue: number;
};

export type ManagerOpsServer = {
  id: number;
  name: string;
  email: string;
  active: boolean;
  is_online: boolean;
  last_seen_at: string | null;
  active_tables: number;
  open_orders: number;
  sales_total: number;
  tips_total: number;
  orders_count: number;
};

export type ManagerOpsSummary = {
  totals: ManagerOpsTotals;
  sales_by_channel: ManagerOpsChannel[];
  top_items: ManagerOpsTopItem[];
  servers: ManagerOpsServer[];
};

export type ServerDashboardSummary = {
  sales_total: number;
  tips_total: number;
  orders_count: number;
  tables_closed: number;
  active_tables: number;
};

export type ServerUser = {
  id: number;
  name: string;
  email: string;
  active: boolean;
  last_login: string | null;
};

export type Dish = {
  id: number;
  name: string;
  description: string;
  price: number;
  category_id: number;
  category_name: string;
  subcategory_id?: number | null;
  subcategory_name?: string | null;
  image: string | null;
  visible: boolean;
  featured_on_cover: boolean;
  position: number;
  type_id?: number | null;
  type_name?: string | null;
  region_id?: number | null;
  region_name?: string | null;
  grapes?: {id: number; name: string}[];
  food_pairings?: {id: number; name: string}[];
  recommended_dishes?: {id: number; name: string}[];
  upsells?: UpsellItem[];
  extras?: ExtraOption[];
  prep_labels?: PrepLabel[];
  taxes?: Tax[];
};

export type UpsellItem = {
  id: number;
  name: string;
  price: number;
  type: 'dish' | 'cocktail' | 'wine';
};

export type ExtraOption = {
  id: number;
  name: string;
  group_name?: string | null;
  group_required?: boolean;
  max_select?: number | null;
  min_select?: number | null;
  kind?: string | null;
  price: number;
  description?: string | null;
  active?: boolean;
};

export type PrepLabel = {
  id: number;
  name: string;
  prep_area_id: number | null;
};

export type Tax = {
  id: number;
  name: string;
  rate: number;
  active: boolean;
};

export type CategoryPayload = {
  id: number;
  name: string;
  order: number;
  dishes: Dish[];
  show_on_cover?: boolean;
  cover_title?: string | null;
  cover_subtitle?: string | null;
};

export type ManagerView = 'menu' | 'cocktails' | 'wines';

export type Campaign = {
  id: number;
  title: string;
  image: string | null;
  view: string;
  start_date: string;
  end_date: string;
  active: boolean;
  repeat_days: number[];
};

export type LoginResponse = {
  token: string;
  user: User;
};

export type VisitPayload = {
  name: string;
  email: string;
  phone: string;
};

export type DishFormInput = {
  name: string;
  description: string;
  price: string;
  category_id: number;
  featured_on_cover?: boolean;
  visible?: boolean;
  type_id?: number | null;
  region_id?: number | null;
  grapes?: number[];
  food_pairings?: number[];
  recommended_dishes?: number[];
  extra_ids?: number[];
  prep_label_ids?: number[];
  tax_ids?: number[];
};

export type CategoryFormInput = {
  name: string;
  show_on_cover?: boolean;
  cover_title?: string | null;
  cover_subtitle?: string | null;
};

export type TableOrderItemExtra = {
  id: number;
  name: string;
  group_name?: string | null;
  kind?: string | null;
  price: number;
  quantity: number;
};

export type TableOrderItemLabel = {
  id: number;
  name: string;
  area_id: number | null;
  area_name: string | null;
  status: string;
  prepared_at: string | null;
  ready_at: string | null;
  delivered_at: string | null;
};

export type TableOrderItem = {
  id: number;
  name: string;
  quantity: number;
  unit_price: number;
  notes: string | null;
  category_scope: string | null;
  category_id: number | null;
  category_name: string | null;
  category_order: number;
  extras: TableOrderItemExtra[];
  labels?: TableOrderItemLabel[];
};

export type TableOrder = {
  id: number;
  order_id?: number;
  status: 'pending' | 'confirmed' | 'cancelled' | string;
  clover_order_id?: string | null;
  clover_status?: string | null;
  clover_total_paid?: number | null;
  created_at: string | null;
  confirmed_at: string | null;
  cancelled_at: string | null;
  items: TableOrderItem[];
};

export type DiningTable = {
  id: number;
  label: string;
  capacity: number;
  section: string | null;
  status: 'available' | 'reserved' | 'occupied' | 'dirty' | 'out_of_service' | string;
  position: number;
  notes: string | null;
  active_assignment?: {
    id: number;
    waiting_list_entry_id: number;
    assigned_at: string | null;
    entry?: {
      id: number;
      guest_name: string;
      party_size: number;
      status: string;
    } | null;
  } | null;
  active_session?: {
    id: number;
    server_id: number | null;
    server_name?: string | null;
    guest_name: string;
    party_size: number;
    seated_at: string | null;
    first_order_at: string | null;
    closed_at: string | null;
    elapsed_minutes: number | null;
    estimated_turn_minutes: number | null;
    remaining_minutes: number | null;
  } | null;
  created_at: string | null;
  updated_at: string | null;
};

export type WaitingListEntry = {
  id: number;
  guest_name: string;
  guest_phone: string;
  guest_email: string | null;
  party_size: number;
  notes: string | null;
  status: 'waiting' | 'notified' | 'seated' | 'cancelled' | 'no_show' | string;
  quoted_minutes: number | null;
  quoted_at: string | null;
  notified_at: string | null;
  seated_at: string | null;
  cancelled_at: string | null;
  no_show_at: string | null;
  tables?: {
    id: number | null;
    label: string | null;
    capacity: number | null;
    status: string | null;
    assigned_at: string | null;
  }[];
  timeclock?: {
    estimated_wait_minutes: number | null;
    elapsed_wait_minutes: number | null;
    remaining_wait_minutes: number | null;
    waited_minutes: number | null;
  } | null;
  created_at: string | null;
  updated_at: string | null;
};

export type WaitingListSettings = {
  id: number;
  default_wait_minutes: number;
  notify_after_minutes: number;
  sms_enabled: boolean;
  email_enabled: boolean;
  notify_message_template: string | null;
};

export type TableSession = {
  id: number;
  open_order_id?: number | null;
  server_id?: number;
  server_name?: string | null;
  table_label: string;
  dining_table_id?: number | null;
  waiting_list_entry_id?: number | null;
  dining_table?: {
    id: number;
    label: string;
    capacity: number;
    section: string | null;
    status: string;
  } | null;
  party_size: number;
  guest_name: string;
  guest_email: string;
  guest_phone: string;
  loyalty_visit_id?: number | null;
  order_mode?: 'traditional' | 'table' | string | null;
  status: 'active' | 'closed' | 'expired' | string;
  service_channel?: string | null;
  seated_at?: string | null;
  first_order_at?: string | null;
  paid_at?: string | null;
  expires_at: string | null;
  closed_at: string | null;
  qr_url: string | null;
  created_at: string | null;
  timeclock?: {
    elapsed_minutes: number | null;
    estimated_turn_minutes: number | null;
    remaining_minutes: number | null;
    elapsed_since_first_order_minutes: number | null;
  };
  payment_summary?: {
    subtotal: number;
    tax_total: number;
    total: number;
    paid_subtotal: number;
    paid_total: number;
    tip_total: number;
    balance: number;
    is_paid: boolean;
  };
  orders?: TableOrder[];
};

export type PosTicket = {
  id: number;
  ticket_id: number | null;
  service_channel: 'walkin' | 'phone' | string;
  status: 'active' | 'closed' | string;
  guest_name: string;
  guest_phone: string;
  guest_email: string;
  party_size: number;
  created_at: string | null;
  payment_summary?: {
    subtotal: number;
    tax_total: number;
    total: number;
    paid_subtotal: number;
    paid_total: number;
    tip_total: number;
    balance: number;
    is_paid: boolean;
  };
  orders?: TableOrder[];
};

export type PrepLabel = {
  id: number;
  name: string;
  slug?: string | null;
  prep_area_id: number;
  area_name?: string | null;
  printer_id?: number | null;
  active?: boolean;
};

export type PrepArea = {
  id: number;
  name: string;
  slug?: string | null;
  color?: string | null;
  active?: boolean;
  is_default?: boolean;
  labels?: PrepLabel[];
};

export type KitchenOrderItemLabel = {
  id: number;
  name: string;
  area_id: number | null;
  area_name: string | null;
  status: string;
};

export type KitchenOrderItemExtra = {
  id: number;
  name: string;
  price: number;
  quantity: number;
};

export type KitchenOrderItem = {
  id: number;
  name: string;
  quantity: number;
  notes: string | null;
  labels: KitchenOrderItemLabel[];
  extras: KitchenOrderItemExtra[];
};

export type KitchenOrder = {
  order_id: number;
  table_label: string | null;
  guest_name: string | null;
  party_size: number | null;
  server_name: string | null;
  created_at: string | null;
  items: KitchenOrderItem[];
};

export type PosTicketPayload = {
  type: 'walkin' | 'phone';
  guest_name?: string;
  guest_phone?: string;
  guest_email?: string;
  party_size?: number;
};

export type TableSessionPayload = {
  table_label?: string;
  dining_table_id?: number | null;
  party_size: number;
  guest_name: string;
  guest_email: string;
  guest_phone: string;
  order_mode?: 'traditional' | 'table';
};

export type ServerOrderItemPayload = {
  type: 'dish' | 'cocktail' | 'wine';
  id: number;
  quantity: number;
  notes?: string;
  extras?: {id: number; quantity?: number}[];
};
