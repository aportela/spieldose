interface MenuItem {
  icon: string;
  text: string;
  routeName: string;
  alternateRouteNames?: string;
  action?: () => void;
};

export { type MenuItem };
