import { type SortOrder } from "./common";

interface Sort {
  field: string;
  order: SortOrder;
}

class SortClass implements Sort {
  field: string;
  order: SortOrder;

  constructor(field: string, order: SortOrder) {
    this.field = field;
    this.order = order;
  }
}

export { type Sort, SortClass };
