import {useMemo} from 'react';
import {useServerSessions} from '../../context/ServerSessionsContext';
import {timeLeft} from '../../utils/serverOrderHelpers';

type TablePriority = 'urgent' | 'warning' | 'normal';

export type TableViewModel = {
  pendingCount: number;
  minutesLeft: number | null;
  priority: TablePriority;
};

export const useTablesViewModel = () => {
  const {runningSessions, loading, error, refresh, refreshing} =
    useServerSessions();

  const tables = useMemo(
    () =>
      runningSessions.map(session => {
        const pendingCount =
          session.orders?.filter(order => order.status === 'pending').length ??
          0;

        const minutesLeft = timeLeft(session.expires_at);

        const priority: TablePriority =
          pendingCount > 0
            ? 'urgent'
            : minutesLeft !== null && minutesLeft <= 5
            ? 'warning'
            : 'normal';

        return {
          ...session,
          pendingCount,
          minutesLeft,
          priority,
        };
      }),
    [runningSessions],
  );

  return {
    loading,
    error,
    refresh,
    refreshing,
    tables,
  };
};
