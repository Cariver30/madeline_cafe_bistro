import React, {useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {NativeStackScreenProps} from '@react-navigation/native-stack';
import {ServerStackParamList} from '../../navigation/serverTypes';
import {useAuth} from '../../context/AuthContext';
import {useServerSessions} from '../../context/ServerSessionsContext';
import {
  confirmServerExternalPayment,
  createServerPayment,
  getTipSettings,
  payServerTableSession,
  sendServerReceipt,
} from '../../services/api';
import {startTapToPaySale} from '../../services/tapToPay';

type Props = NativeStackScreenProps<ServerStackParamList, 'ServerPayment'>;
type PaymentMethod = 'cash' | 'card' | 'ath' | 'split' | 'tap_to_pay';

const methods: {id: PaymentMethod; label: string}[] = [
  {id: 'cash', label: 'Cash'},
  {id: 'card', label: 'Tarjeta'},
  {id: 'ath', label: 'ATH Movil'},
  {id: 'split', label: 'Combinado'},
  {id: 'tap_to_pay', label: 'Tap to Pay'},
];

const ServerPaymentScreen = ({navigation, route}: Props) => {
  const {sessionId} = route.params;
  const {token} = useAuth();
  const {getSessionById, loadSessions} = useServerSessions();
  const session = getSessionById(sessionId);
  const [paymentMode, setPaymentMode] = useState<'single' | 'split'>('single');
  const [selected, setSelected] = useState<PaymentMethod>('cash');
  const [tipPresets, setTipPresets] = useState<number[]>([]);
  const [allowCustomTip, setAllowCustomTip] = useState(true);
  const [allowSkipTip, setAllowSkipTip] = useState(false);
  const [selectedTipPercent, setSelectedTipPercent] = useState<number | null>(null);
  const [selectedSplitTipPercent, setSelectedSplitTipPercent] = useState<number | null>(null);
  const [splitMethod, setSplitMethod] = useState<'cash' | 'card' | 'ath'>('cash');
  const [splitMode, setSplitMode] = useState<'items' | 'amount'>('items');
  const [tip, setTip] = useState('');
  const [cashReceived, setCashReceived] = useState('');
  const [cashEdited, setCashEdited] = useState(false);
  const [splitAmount, setSplitAmount] = useState('');
  const [splitTipPercent, setSplitTipPercent] = useState('');
  const [splitCashReceived, setSplitCashReceived] = useState('');
  const [splitCashEdited, setSplitCashEdited] = useState(false);
  const [splitItemIds, setSplitItemIds] = useState<number[]>([]);
  const [splitError, setSplitError] = useState<string | null>(null);
  const [terminalError, setTerminalError] = useState<string | null>(null);
  const [terminalStatus, setTerminalStatus] = useState<string | null>(null);
  const [paying, setPaying] = useState(false);
  const [receiptNotice, setReceiptNotice] = useState<string | null>(null);
  const [sendingReceipt, setSendingReceipt] = useState(false);

  const buildProcessorPayload = (
    result: {
      transaction_id?: string | null;
      masked_pan?: string | null;
      card_type?: string | null;
      kernel_result?: string | null;
      emv_accepted?: boolean;
    } | null,
    amount: number,
  ) => {
    const masked = result?.masked_pan ?? '';
    const digits = masked.replace(/\D/g, '');
    const last4 = digits.length >= 4 ? digits.slice(-4) : undefined;
    return {
      status: 'APPROVED',
      respCode: '00',
      transaction_id: result?.transaction_id ?? `SIM-${Date.now()}`,
      card_type: result?.card_type ?? 'NFC',
      entry_type: 'tap-on-phone',
      last_4_digits: last4,
      amount: amount.toFixed(2),
      emv_accepted: result?.emv_accepted ?? undefined,
      kernel_result: result?.kernel_result ?? undefined,
    };
  };

  useEffect(() => {
    let active = true;
    if (!token) {
      return () => {
        active = false;
      };
    }

    getTipSettings(token)
      .then(settings => {
        if (!active) {
          return;
        }
        setTipPresets(settings.presets ?? []);
        setAllowCustomTip(settings.allow_custom ?? true);
        setAllowSkipTip(settings.allow_skip ?? false);
      })
      .catch(() => {
        // keep existing defaults when settings are unavailable
      });

    return () => {
      active = false;
    };
  }, [token]);

  useEffect(() => {
    if (!allowCustomTip) {
      setTip('');
      setSplitTipPercent('');
    }
  }, [allowCustomTip]);

  const receiptLines = useMemo(() => {
    if (!session?.orders?.length) {
      return [];
    }
    const lines: {key: string; label: string; amount: number; muted?: boolean}[] = [];
    session.orders.forEach(order => {
      if (order.status === 'cancelled') {
        return;
      }
      order.items.forEach(item => {
        const baseTotal = Number(item.unit_price ?? 0) * item.quantity;
        lines.push({
          key: `item-${order.id}-${item.id}`,
          label: `${item.quantity}x ${item.name}`,
          amount: baseTotal,
        });
        (item.extras ?? []).forEach(extra => {
          const extraQty = extra.quantity ?? 1;
          const extraTotal = Number(extra.price ?? 0) * extraQty;
          lines.push({
            key: `extra-${order.id}-${item.id}-${extra.id}`,
            label: `+ ${extra.name}${extraQty > 1 ? ` x${extraQty}` : ''}`,
            amount: extraTotal,
            muted: true,
          });
        });
      });
    });
    return lines;
  }, [session]);

  const subtotal = useMemo(() => {
    if (!session?.orders?.length) {
      return 0;
    }
    const total = session.orders.reduce((sum, order) => {
      if (order.status === 'cancelled') {
        return sum;
      }
      const orderTotal = order.items.reduce((itemSum, item) => {
        const base = Number(item.unit_price ?? 0) * item.quantity;
        const extras = (item.extras ?? []).reduce((extraSum, extra) => {
          const extraPrice = Number(extra.price ?? 0);
          const extraQty = extra.quantity ?? 1;
          return extraSum + extraPrice * extraQty;
        }, 0);
        return itemSum + base + extras;
      }, 0);
      return sum + orderTotal;
    }, 0);
    return Number(total.toFixed(2));
  }, [session]);

  const summarySubtotal = session?.payment_summary?.subtotal ?? subtotal;
  const summaryTaxTotal = session?.payment_summary?.tax_total ?? 0;
  const summaryTotal =
    session?.payment_summary?.total ??
    Number((summarySubtotal + summaryTaxTotal).toFixed(2));

  const {tipValue, tipInvalid} = useMemo(() => {
    if (allowCustomTip) {
      const normalized = tip.trim().replace(',', '.');
      if (normalized) {
        const parsed = Number(normalized);
        if (Number.isNaN(parsed)) {
          return {tipValue: undefined, tipInvalid: true};
        }
        return {tipValue: parsed, tipInvalid: false};
      }
    }

    if (selectedTipPercent !== null) {
      const value = Number(
        (summarySubtotal * (selectedTipPercent / 100)).toFixed(2),
      );
      return {tipValue: value, tipInvalid: false};
    }

    return {tipValue: undefined, tipInvalid: false};
  }, [allowCustomTip, tip, selectedTipPercent, summarySubtotal]);

  const {splitTipPercentValue, splitTipPercentInvalid} = useMemo(() => {
    if (allowCustomTip) {
      const normalized = splitTipPercent.trim().replace(',', '.');
      if (normalized) {
        const parsed = Number(normalized);
        if (Number.isNaN(parsed) || parsed < 0 || parsed > 100) {
          return {splitTipPercentValue: undefined, splitTipPercentInvalid: true};
        }
        return {splitTipPercentValue: parsed, splitTipPercentInvalid: false};
      }
    }

    if (selectedSplitTipPercent !== null) {
      return {splitTipPercentValue: selectedSplitTipPercent, splitTipPercentInvalid: false};
    }

    return {splitTipPercentValue: undefined, splitTipPercentInvalid: false};
  }, [allowCustomTip, splitTipPercent, selectedSplitTipPercent]);

  const paidTotal = session?.payment_summary?.paid_total ?? 0;
  const balanceDue = Number(Math.max(summaryTotal - paidTotal, 0).toFixed(2));
  const totalDue = useMemo(() => {
    const extra = tipValue ?? 0;
    return Number((balanceDue + extra).toFixed(2));
  }, [balanceDue, tipValue]);

  const {cashValue, cashInvalid, cashShort} = useMemo(() => {
    if (selected !== 'cash') {
      return {cashValue: undefined, cashInvalid: false, cashShort: false};
    }
    const normalized = cashReceived.trim().replace(',', '.');
    if (!normalized) {
      return {cashValue: undefined, cashInvalid: false, cashShort: false};
    }
    const parsed = Number(normalized);
    if (Number.isNaN(parsed)) {
      return {cashValue: undefined, cashInvalid: true, cashShort: false};
    }
    return {
      cashValue: parsed,
      cashInvalid: false,
      cashShort: parsed < totalDue,
    };
  }, [cashReceived, selected, totalDue]);

  const changeDue =
    cashValue !== undefined && cashValue >= totalDue
      ? Number((cashValue - totalDue).toFixed(2))
      : null;

  const splitItems = useMemo(() => {
    if (!session?.orders?.length) {
      return [];
    }
    const items: {
      id: number;
      name: string;
      quantity: number;
      total: number;
    }[] = [];
    session.orders.forEach(order => {
      if (order.status === 'cancelled') {
        return;
      }
      order.items.forEach(item => {
        const base = Number(item.unit_price ?? 0) * item.quantity;
        const extras = (item.extras ?? []).reduce((extraSum, extra) => {
          const extraPrice = Number(extra.price ?? 0);
          const extraQty = extra.quantity ?? 1;
          return extraSum + extraPrice * extraQty;
        }, 0);
        items.push({
          id: item.id,
          name: item.name,
          quantity: item.quantity,
          total: Number((base + extras).toFixed(2)),
        });
      });
    });
    return items;
  }, [session]);

  const splitSubtotal = useMemo(() => {
    if (splitMode === 'items') {
      const selectedIds = new Set(splitItemIds);
      return splitItems
        .filter(item => selectedIds.has(item.id))
        .reduce((sum, item) => sum + item.total, 0);
    }
    const normalized = splitAmount.trim().replace(',', '.');
    const parsed = Number(normalized);
    if (Number.isNaN(parsed)) {
      return 0;
    }
    return Math.max(parsed, 0);
  }, [splitAmount, splitItemIds, splitItems, splitMode]);

  const splitTipValue = useMemo(() => {
    if (splitTipPercentValue === undefined) {
      return 0;
    }
    return Number((splitSubtotal * (splitTipPercentValue / 100)).toFixed(2));
  }, [splitSubtotal, splitTipPercentValue]);

  const splitTotalDue = Number((splitSubtotal + splitTipValue).toFixed(2));

  const {splitCashValue, splitCashInvalid, splitCashShort} = useMemo(() => {
    if (splitMethod !== 'cash') {
      return {splitCashValue: undefined, splitCashInvalid: false, splitCashShort: false};
    }
    const normalized = splitCashReceived.trim().replace(',', '.');
    if (!normalized) {
      return {splitCashValue: undefined, splitCashInvalid: false, splitCashShort: false};
    }
    const parsed = Number(normalized);
    if (Number.isNaN(parsed)) {
      return {splitCashValue: undefined, splitCashInvalid: true, splitCashShort: false};
    }
    return {
      splitCashValue: parsed,
      splitCashInvalid: false,
      splitCashShort: parsed < splitTotalDue,
    };
  }, [splitCashReceived, splitMethod, splitTotalDue]);

  const splitChangeDue =
    splitCashValue !== undefined && splitCashValue >= splitTotalDue
      ? Number((splitCashValue - splitTotalDue).toFixed(2))
      : null;

  useEffect(() => {
    if (selected !== 'cash') {
      setCashEdited(false);
      setCashReceived('');
      return;
    }
    if (!cashEdited && !cashReceived && totalDue > 0) {
      setCashReceived(totalDue.toFixed(2));
    }
  }, [selected, cashReceived, totalDue, cashEdited]);

  useEffect(() => {
    if (paymentMode !== 'split' || splitMethod !== 'cash') {
      setSplitCashEdited(false);
      setSplitCashReceived('');
      return;
    }
    if (!splitCashEdited && !splitCashReceived && splitTotalDue > 0) {
      setSplitCashReceived(splitTotalDue.toFixed(2));
    }
  }, [paymentMode, splitMethod, splitCashReceived, splitTotalDue, splitCashEdited]);

  useEffect(() => {
    if (paymentMode === 'split' && splitMode === 'amount' && !splitAmount && balanceDue > 0) {
      setSplitAmount(balanceDue.toFixed(2));
    }
  }, [paymentMode, splitMode, splitAmount, balanceDue]);

  const handleTapToPay = async () => {
    if (!token) {
      setTerminalError('Debes iniciar sesión para cobrar.');
      return;
    }
    if (!session?.open_order_id) {
      setTerminalError('No hay una orden abierta para cobrar.');
      return;
    }
    if (totalDue <= 0) {
      setTerminalError('No hay monto pendiente para cobrar.');
      return;
    }

    setPaying(true);
    setTerminalError(null);
    setTerminalStatus('Preparando Tap to Pay...');

    try {
      setTerminalStatus('Esperando tarjeta...');
      let processorPayload: Record<string, unknown>;
      try {
        const result = await startTapToPaySale({
          token,
          amount: totalDue,
          reference: String(session.open_order_id),
        });
        processorPayload = buildProcessorPayload(result, totalDue);
      } catch (err) {
        if (!__DEV__) {
          throw err;
        }
        setTerminalStatus('Modo demo Tap to Pay.');
        processorPayload = buildProcessorPayload(null, totalDue);
      }

      setTerminalStatus('Pago completado.');
      await confirmServerExternalPayment(token, session.id, {
        method: 'tap_to_pay',
        provider: 'dejavoo',
        payload: processorPayload,
        tip: tipValue,
        amount: totalDue,
      });
      await loadSessions(false);
      navigation.popToTop();
    } catch (err) {
      setTerminalError(
        err instanceof Error ? err.message : 'No se pudo procesar el pago.',
      );
      setTerminalStatus('No se pudo completar el cobro.');
    } finally {
      setPaying(false);
    }
  };

  const handlePay = async () => {
    if (tipInvalid || !token || !session) {
      return;
    }
    setSplitError(null);
    if (paymentMode === 'split') {
      if (splitTipPercentInvalid) {
        setSplitError('Ingresa un porcentaje válido.');
        return;
      }
      if (splitMode === 'items' && splitItemIds.length === 0) {
        setSplitError('Selecciona los platos que van en este pago.');
        return;
      }
      if (splitMode === 'amount' && splitSubtotal <= 0) {
        setSplitError('Ingresa un monto válido.');
        return;
      }
      if (splitSubtotal > balanceDue) {
        setSplitError('El monto excede lo pendiente.');
        return;
      }
      if (splitMethod === 'cash' && (splitCashInvalid || splitCashValue === undefined || splitCashShort)) {
        setSplitError('El efectivo recibido es menor al total.');
        return;
      }

      setPaying(true);
      setTerminalError(null);
      try {
        await createServerPayment(token, session.id, {
          method: splitMethod,
          split_mode: splitMode,
          items: splitMode === 'items' ? splitItemIds : undefined,
          amount: splitMode === 'amount' ? splitSubtotal : undefined,
          tip_percent: splitTipPercentValue,
        });
        await loadSessions(false);
        const updated = getSessionById(sessionId);
        if (updated?.payment_summary?.is_paid) {
          navigation.popToTop();
        }
      } catch (err) {
        setTerminalError(
          err instanceof Error ? err.message : 'No se pudo cobrar.',
        );
      } finally {
        setPaying(false);
      }
      return;
    }
    if (selected === 'tap_to_pay') {
      await handleTapToPay();
      return;
    }
    setPaying(true);
    setTerminalError(null);
    try {
      await payServerTableSession(token, session.id, selected, tipValue);
      navigation.popToTop();
    } catch (err) {
      setTerminalError(
        err instanceof Error ? err.message : 'No se pudo cobrar.',
      );
    } finally {
      setPaying(false);
    }
  };

  const handleSendReceipt = async () => {
    if (!token || !session) {
      return;
    }
    setSendingReceipt(true);
    setReceiptNotice(null);
    try {
      const response = await sendServerReceipt(token, session.id);
      setReceiptNotice(response.message ?? 'Cuenta enviada.');
    } catch (err) {
      setReceiptNotice(
        err instanceof Error ? err.message : 'No se pudo enviar la cuenta.',
      );
    } finally {
      setSendingReceipt(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled">
        <View style={styles.card}>
          <Text style={styles.title}>Cobrar mesa</Text>
          <Text style={styles.subtitle}>Selecciona método de pago</Text>
          <View style={styles.modeRow}>
            <TouchableOpacity
              style={[styles.modeButton, paymentMode === 'single' && styles.modeButtonActive]}
              onPress={() => setPaymentMode('single')}>
              <Text style={[styles.modeText, paymentMode === 'single' && styles.modeTextActive]}>
                Cobro completo
              </Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.modeButton, paymentMode === 'split' && styles.modeButtonActive]}
              onPress={() => setPaymentMode('split')}>
              <Text style={[styles.modeText, paymentMode === 'split' && styles.modeTextActive]}>
                Split
              </Text>
            </TouchableOpacity>
          </View>
          <View style={styles.receiptBox}>
            <View style={styles.receiptHeader}>
              <View>
                <Text style={styles.receiptTitle}>Resumen de la mesa</Text>
                <Text style={styles.receiptMeta}>
                  {receiptLines.length} items · ${summarySubtotal.toFixed(2)}
                </Text>
              </View>
              <TouchableOpacity
                style={[
                  styles.receiptButton,
                  sendingReceipt && styles.receiptButtonDisabled,
                ]}
                onPress={handleSendReceipt}
                disabled={sendingReceipt}>
                {sendingReceipt ? (
                  <ActivityIndicator color="#0f172a" />
                ) : (
                  <Text style={styles.receiptButtonText}>Enviar cuenta</Text>
                )}
              </TouchableOpacity>
            </View>
            {receiptNotice ? (
              <Text style={styles.receiptNotice}>{receiptNotice}</Text>
            ) : null}
            <ScrollView
              style={styles.receiptList}
              contentContainerStyle={styles.receiptListContent}>
              {receiptLines.length ? (
                receiptLines.map(line => (
                  <View key={line.key} style={styles.receiptRow}>
                    <Text
                      style={[
                        styles.receiptLabel,
                        line.muted && styles.receiptLabelMuted,
                      ]}>
                      {line.label}
                    </Text>
                    <Text
                      style={[
                        styles.receiptAmount,
                        line.muted && styles.receiptLabelMuted,
                      ]}>
                      ${line.amount.toFixed(2)}
                    </Text>
                  </View>
                ))
              ) : (
                <Text style={styles.emptyText}>Sin items para cobrar.</Text>
              )}
            </ScrollView>
            <View style={styles.receiptTotals}>
              <View style={styles.receiptRow}>
                <Text style={styles.receiptLabel}>Subtotal</Text>
                <Text style={styles.receiptAmount}>
                  ${summarySubtotal.toFixed(2)}
                </Text>
              </View>
              <View style={styles.receiptRow}>
                <Text style={styles.receiptLabel}>Impuestos</Text>
                <Text style={styles.receiptAmount}>
                  ${summaryTaxTotal.toFixed(2)}
                </Text>
              </View>
              <View style={styles.receiptRow}>
                <Text style={styles.receiptLabel}>Propina</Text>
                <Text style={styles.receiptAmount}>
                  ${Number(tipValue ?? 0).toFixed(2)}
                </Text>
              </View>
              <View style={styles.receiptRow}>
                <Text style={styles.receiptTotalLabel}>Total</Text>
                <Text style={styles.receiptTotalLabel}>
                  ${totalDue.toFixed(2)}
                </Text>
              </View>
              <View style={styles.receiptRow}>
                <Text style={styles.receiptLabel}>Pagado</Text>
                <Text style={styles.receiptAmount}>
                  ${paidTotal.toFixed(2)}
                </Text>
              </View>
              <View style={styles.receiptRow}>
                <Text style={styles.receiptLabel}>Pendiente</Text>
                <Text style={styles.receiptAmount}>${balanceDue.toFixed(2)}</Text>
              </View>
            </View>
          </View>
          {paymentMode === 'single' ? (
            <View style={styles.methodList}>
              {methods.map(method => (
                <TouchableOpacity
                  key={method.id}
                  style={[
                    styles.methodButton,
                    selected === method.id && styles.methodActive,
                  ]}
                  onPress={() => {
                    setSelected(method.id);
                    if (method.id !== 'tap_to_pay') {
                      setTerminalError(null);
                      setTerminalStatus(null);
                    }
                  }}>
                  <Text
                    style={[
                      styles.methodText,
                      selected === method.id && styles.methodTextActive,
                    ]}>
                    {method.label}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          ) : (
            <>
              <View style={styles.methodList}>
                {(['cash', 'card', 'ath'] as const).map(method => (
                  <TouchableOpacity
                    key={method}
                    style={[
                      styles.methodButton,
                      splitMethod === method && styles.methodActive,
                    ]}
                    onPress={() => setSplitMethod(method)}>
                    <Text
                      style={[
                        styles.methodText,
                        splitMethod === method && styles.methodTextActive,
                      ]}>
                      {method === 'cash' ? 'Cash' : method === 'card' ? 'Tarjeta' : 'ATH Movil'}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>
              <View style={styles.splitModeRow}>
                <TouchableOpacity
                  style={[styles.modeButton, splitMode === 'items' && styles.modeButtonActive]}
                  onPress={() => setSplitMode('items')}>
                  <Text style={[styles.modeText, splitMode === 'items' && styles.modeTextActive]}>
                    Por platos
                  </Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={[styles.modeButton, splitMode === 'amount' && styles.modeButtonActive]}
                  onPress={() => setSplitMode('amount')}>
                  <Text style={[styles.modeText, splitMode === 'amount' && styles.modeTextActive]}>
                    Por monto
                  </Text>
                </TouchableOpacity>
              </View>
              {splitMode === 'items' ? (
                <View style={styles.splitItemsBox}>
                  {splitItems.length ? (
                    splitItems.map(item => {
                      const selectedItem = splitItemIds.includes(item.id);
                      return (
                        <TouchableOpacity
                          key={`split-item-${item.id}`}
                          style={[
                            styles.splitItemRow,
                            selectedItem && styles.splitItemRowActive,
                          ]}
                          onPress={() => {
                            setSplitItemIds(prev =>
                              prev.includes(item.id)
                                ? prev.filter(id => id !== item.id)
                                : [...prev, item.id],
                            );
                          }}>
                          <Text style={styles.splitItemText}>
                            {item.quantity}x {item.name}
                          </Text>
                          <Text style={styles.splitItemAmount}>
                            ${item.total.toFixed(2)}
                          </Text>
                        </TouchableOpacity>
                      );
                    })
                  ) : (
                    <Text style={styles.emptyText}>No hay items disponibles.</Text>
                  )}
                </View>
              ) : (
                <View style={styles.cashRow}>
                  <Text style={styles.tipLabel}>Monto a cobrar (sin propina)</Text>
                  <TextInput
                    style={styles.tipInput}
                    placeholder="$0.00"
                    placeholderTextColor="#94a3b8"
                    value={splitAmount}
                    onChangeText={setSplitAmount}
                    keyboardType="decimal-pad"
                  />
                </View>
              )}
              <View style={styles.tipRow}>
                <Text style={styles.tipLabel}>Propina (este split)</Text>
                <View style={styles.tipOptions}>
                  {tipPresets.map(percent => {
                    const active = selectedSplitTipPercent === percent;
                    return (
                      <TouchableOpacity
                        key={`split-tip-${percent}`}
                        style={[styles.tipChip, active && styles.tipChipActive]}
                        onPress={() => {
                          setSelectedSplitTipPercent(percent);
                          setSplitTipPercent('');
                        }}>
                        <Text style={[styles.tipChipText, active && styles.tipChipTextActive]}>
                          {percent}%
                        </Text>
                      </TouchableOpacity>
                    );
                  })}
                  {allowSkipTip ? (
                    <TouchableOpacity
                      style={[
                        styles.tipChip,
                        styles.tipSkipChip,
                        selectedSplitTipPercent === 0 && styles.tipChipActive,
                      ]}
                      onPress={() => {
                        setSelectedSplitTipPercent(0);
                        setSplitTipPercent('');
                      }}>
                      <Text
                        style={[
                          styles.tipChipText,
                          selectedSplitTipPercent === 0 && styles.tipChipTextActive,
                        ]}>
                        Sin propina
                      </Text>
                    </TouchableOpacity>
                  ) : null}
                </View>
                {allowCustomTip ? (
                  <View style={styles.tipCustomRow}>
                    <Text style={styles.tipCustomLabel}>Personalizado %</Text>
                    <TextInput
                      style={[styles.tipInput, splitTipPercentInvalid && styles.tipInputError]}
                      placeholder="0"
                      placeholderTextColor="#94a3b8"
                      value={splitTipPercent}
                      onChangeText={value => {
                        setSplitTipPercent(value);
                        if (value.trim()) {
                          setSelectedSplitTipPercent(null);
                        }
                      }}
                      keyboardType="decimal-pad"
                    />
                  </View>
                ) : null}
                {splitTipPercentInvalid ? (
                  <Text style={styles.error}>Ingresa un porcentaje válido.</Text>
                ) : null}
              </View>
              <View style={styles.cashSummary}>
                <Text style={styles.cashMeta}>Subtotal: ${splitSubtotal.toFixed(2)}</Text>
                <Text style={styles.cashMeta}>Propina: ${splitTipValue.toFixed(2)}</Text>
                <Text style={styles.cashMeta}>Total: ${splitTotalDue.toFixed(2)}</Text>
              </View>
              {splitMethod === 'cash' ? (
                <View style={styles.cashRow}>
                  <Text style={styles.tipLabel}>Efectivo recibido</Text>
                    <TextInput
                      style={[styles.tipInput, splitCashInvalid && styles.tipInputError]}
                      placeholder="$0.00"
                      placeholderTextColor="#94a3b8"
                      value={splitCashReceived}
                      onChangeText={value => {
                        setSplitCashEdited(true);
                        setSplitCashReceived(value);
                      }}
                      keyboardType="decimal-pad"
                    />
                  <View style={styles.cashSummary}>
                    <Text style={styles.cashMeta}>
                      Cambio: {splitChangeDue !== null ? `$${splitChangeDue.toFixed(2)}` : '—'}
                    </Text>
                  </View>
                </View>
              ) : null}
              {splitError ? <Text style={styles.error}>{splitError}</Text> : null}
            </>
          )}
          {paymentMode === 'single' && selected === 'tap_to_pay' ? (
            <View style={styles.terminalStatusBox}>
              <Text style={styles.terminalStatusLabel}>Estado del lector</Text>
              <Text style={styles.terminalStatusText}>
                {terminalStatus ?? 'Listo para cobrar.'}
              </Text>
            </View>
          ) : null}
          {paymentMode === 'single' ? (
            <View style={styles.tipRow}>
              <Text style={styles.tipLabel}>Propina</Text>
              <View style={styles.tipOptions}>
                {tipPresets.map(percent => {
                  const active = selectedTipPercent === percent;
                  return (
                    <TouchableOpacity
                      key={`tip-${percent}`}
                      style={[styles.tipChip, active && styles.tipChipActive]}
                      onPress={() => {
                        setSelectedTipPercent(percent);
                        setTip('');
                      }}>
                      <Text style={[styles.tipChipText, active && styles.tipChipTextActive]}>
                        {percent}%
                      </Text>
                    </TouchableOpacity>
                  );
                })}
                {allowSkipTip ? (
                  <TouchableOpacity
                    style={[
                      styles.tipChip,
                      styles.tipSkipChip,
                      selectedTipPercent === 0 && styles.tipChipActive,
                    ]}
                    onPress={() => {
                      setSelectedTipPercent(0);
                      setTip('');
                    }}>
                    <Text
                      style={[
                        styles.tipChipText,
                        selectedTipPercent === 0 && styles.tipChipTextActive,
                      ]}>
                      Sin propina
                    </Text>
                  </TouchableOpacity>
                ) : null}
              </View>
              {allowCustomTip ? (
                <View style={styles.tipCustomRow}>
                  <Text style={styles.tipCustomLabel}>Personalizado</Text>
                  <TextInput
                    style={[styles.tipInput, tipInvalid && styles.tipInputError]}
                    placeholder="$0.00"
                    placeholderTextColor="#94a3b8"
                    value={tip}
                    onChangeText={value => {
                      setTip(value);
                      if (value.trim()) {
                        setSelectedTipPercent(null);
                      }
                    }}
                    keyboardType="decimal-pad"
                  />
                </View>
              ) : null}
              {tipInvalid ? (
                <Text style={styles.error}>Ingresa un monto válido.</Text>
              ) : null}
            </View>
          ) : null}
          {paymentMode === 'single' && selected === 'cash' ? (
            <View style={styles.cashRow}>
              <Text style={styles.tipLabel}>Efectivo recibido</Text>
              <TextInput
                style={[styles.tipInput, cashInvalid && styles.tipInputError]}
                placeholder="$0.00"
                placeholderTextColor="#94a3b8"
                value={cashReceived}
                onChangeText={value => {
                  setCashEdited(true);
                  setCashReceived(value);
                }}
                keyboardType="decimal-pad"
              />
              <View style={styles.cashSummary}>
                <Text style={styles.cashMeta}>Total: ${totalDue.toFixed(2)}</Text>
                <Text style={styles.cashMeta}>
                  Cambio: {changeDue !== null ? `$${changeDue.toFixed(2)}` : '—'}
                </Text>
              </View>
              {cashShort ? (
                <Text style={styles.error}>
                  El efectivo recibido es menor al total.
                </Text>
              ) : null}
            </View>
          ) : null}
          {terminalError ? <Text style={styles.error}>{terminalError}</Text> : null}
          <TouchableOpacity
            style={styles.payButton}
            onPress={handlePay}
            disabled={
              paying ||
              tipInvalid ||
              (paymentMode === 'single' &&
                selected === 'cash' &&
                (cashInvalid || cashValue === undefined || cashShort))
            }>
            {paying ? (
              <ActivityIndicator color="#0f172a" />
            ) : (
              <Text style={styles.payText}>
                {paymentMode === 'split' ? 'Registrar pago' : 'Confirmar cobro'}
              </Text>
            )}
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#020617',
  },
  scrollContent: {
    padding: 20,
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
  title: {
    color: '#f8fafc',
    fontSize: 18,
    fontWeight: '700',
  },
  subtitle: {
    color: '#94a3b8',
    fontSize: 12,
  },
  methodList: {
    gap: 8,
  },
  modeRow: {
    flexDirection: 'row',
    gap: 8,
  },
  modeButton: {
    flex: 1,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#334155',
    paddingVertical: 10,
    alignItems: 'center',
  },
  modeButtonActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  modeText: {
    color: '#94a3b8',
    fontWeight: '600',
  },
  modeTextActive: {
    color: '#0f172a',
  },
  tipRow: {
    gap: 6,
  },
  tipLabel: {
    color: '#94a3b8',
    fontSize: 12,
    fontWeight: '600',
  },
  tipInput: {
    backgroundColor: '#1e293b',
    borderRadius: 16,
    paddingHorizontal: 16,
    paddingVertical: 10,
    color: '#f8fafc',
    fontSize: 14,
  },
  tipOptions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  tipChip: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  tipChipActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  tipChipText: {
    color: '#e2e8f0',
    fontSize: 12,
    fontWeight: '600',
  },
  tipChipTextActive: {
    color: '#0f172a',
  },
  tipSkipChip: {
    borderStyle: 'dashed',
  },
  tipCustomRow: {
    gap: 6,
  },
  tipCustomLabel: {
    color: '#94a3b8',
    fontSize: 12,
    fontWeight: '600',
  },
  tipInputError: {
    borderWidth: 1,
    borderColor: '#fb7185',
  },
  cashRow: {
    gap: 8,
  },
  cashSummary: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  cashMeta: {
    color: '#94a3b8',
    fontSize: 12,
    fontWeight: '600',
  },
  splitModeRow: {
    flexDirection: 'row',
    gap: 8,
  },
  splitItemsBox: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#1e293b',
    backgroundColor: '#0b1220',
    padding: 10,
    gap: 8,
  },
  splitItemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 8,
    paddingHorizontal: 10,
    borderRadius: 12,
    backgroundColor: '#0f172a',
    borderWidth: 1,
    borderColor: '#1e293b',
  },
  splitItemRowActive: {
    borderColor: '#fbbf24',
    backgroundColor: '#111827',
  },
  splitItemText: {
    color: '#e2e8f0',
    fontSize: 12,
    flex: 1,
  },
  splitItemAmount: {
    color: '#fbbf24',
    fontSize: 12,
    fontWeight: '600',
  },
  methodButton: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#334155',
    paddingVertical: 10,
    alignItems: 'center',
  },
  methodActive: {
    backgroundColor: '#fbbf24',
    borderColor: '#fbbf24',
  },
  methodText: {
    color: '#94a3b8',
    fontWeight: '600',
  },
  methodTextActive: {
    color: '#0f172a',
  },
  payButton: {
    backgroundColor: '#22c55e',
    borderRadius: 999,
    paddingVertical: 12,
    alignItems: 'center',
  },
  payText: {
    color: '#0f172a',
    fontWeight: '700',
  },
  error: {
    color: '#fb7185',
    fontSize: 12,
  },
  terminalStatusBox: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#1e293b',
    padding: 12,
    backgroundColor: '#0b1220',
    gap: 4,
  },
  terminalStatusLabel: {
    color: '#94a3b8',
    fontSize: 11,
    textTransform: 'uppercase',
    letterSpacing: 1.2,
  },
  terminalStatusText: {
    color: '#e2e8f0',
    fontSize: 13,
    fontWeight: '600',
  },
  receiptBox: {
    backgroundColor: '#0b1220',
    borderRadius: 16,
    padding: 12,
    borderWidth: 1,
    borderColor: '#1e293b',
    gap: 8,
  },
  receiptHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 12,
  },
  receiptTitle: {
    color: '#f8fafc',
    fontSize: 14,
    fontWeight: '700',
  },
  receiptMeta: {
    color: '#94a3b8',
    fontSize: 11,
  },
  receiptButton: {
    backgroundColor: '#fbbf24',
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  receiptButtonDisabled: {
    opacity: 0.6,
  },
  receiptButtonText: {
    color: '#0f172a',
    fontSize: 12,
    fontWeight: '700',
  },
  receiptNotice: {
    color: '#fbbf24',
    fontSize: 12,
  },
  receiptList: {
    maxHeight: 220,
  },
  receiptListContent: {
    gap: 6,
  },
  receiptRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 12,
  },
  receiptLabel: {
    color: '#e2e8f0',
    fontSize: 12,
    flex: 1,
  },
  receiptLabelMuted: {
    color: '#94a3b8',
  },
  receiptAmount: {
    color: '#e2e8f0',
    fontSize: 12,
  },
  receiptTotals: {
    borderTopWidth: 1,
    borderTopColor: '#1e293b',
    paddingTop: 8,
    gap: 6,
  },
  receiptTotalLabel: {
    color: '#fbbf24',
    fontSize: 13,
    fontWeight: '700',
  },
  emptyText: {
    color: '#94a3b8',
    fontSize: 12,
  },
});

export default ServerPaymentScreen;
