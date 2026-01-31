export type ServerStackParamList = {
  ServerHome: undefined;
  TableDetail: {sessionId: number};
  OrderDetail: {orderId: number; sessionId: number};
  NewTable: undefined;
  TakeOrder: {sessionId: number};
  ServerPayment: {sessionId: number};
};

export type ServerTabParamList = {
  Dashboard: undefined;
  Loyalty: undefined;
  PendingOrders: undefined;
  Tables: undefined;
};
