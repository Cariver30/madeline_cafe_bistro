import React from 'react';
import Svg, {Path, Rect} from 'react-native-svg';

type TabIconName =
  | 'orders'
  | 'tables'
  | 'dashboard'
  | 'menu'
  | 'pos'
  | 'campaigns'
  | 'loyalty';

type TabIconProps = {
  name: TabIconName;
  color: string;
  size?: number;
};

const TabIcon = ({name, color, size = 22}: TabIconProps) => {
  const common = {
    stroke: color,
    strokeWidth: 1.8,
    strokeLinecap: 'round' as const,
    strokeLinejoin: 'round' as const,
  };

  switch (name) {
    case 'orders':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
          <Path
            d="M6 4h12a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2"
            {...common}
          />
          <Path d="M8 9h8M8 13h8M8 17h5" {...common} />
        </Svg>
      );
    case 'tables':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
          <Rect x="4" y="4" width="7" height="7" rx="1.5" {...common} />
          <Rect x="13" y="4" width="7" height="7" rx="1.5" {...common} />
          <Rect x="4" y="13" width="7" height="7" rx="1.5" {...common} />
          <Rect x="13" y="13" width="7" height="7" rx="1.5" {...common} />
        </Svg>
      );
    case 'dashboard':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
          <Path d="M4 20h16" {...common} />
          <Path d="M7 20V10" {...common} />
          <Path d="M12 20V6" {...common} />
          <Path d="M17 20v-8" {...common} />
        </Svg>
      );
    case 'menu':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
          <Path d="M4 7h16M4 12h16M4 17h16" {...common} />
        </Svg>
      );
    case 'pos':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
          <Path
            d="M4 7h16a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2"
            {...common}
          />
          <Path d="M2 11h20M6 15h4" {...common} />
        </Svg>
      );
    case 'campaigns':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
          <Path
            d="M12 4l2.6 5.3 5.8.8-4.2 4.1 1 5.9L12 17l-5.2 3 1-5.9-4.2-4.1 5.8-.8L12 4z"
            {...common}
          />
        </Svg>
      );
    case 'loyalty':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
          <Path
            d="M12 3.6l2.5 5 5.5.8-4 3.9.9 5.5-4.9-2.6-4.9 2.6.9-5.5-4-3.9 5.5-.8L12 3.6z"
            {...common}
          />
        </Svg>
      );
    default:
      return null;
  }
};

export default TabIcon;
