import { computed } from "vue";
import { useI18n } from "vue-i18n";

export function useFormUtils() {
  const { t } = useI18n();

  const fieldIsRequiredLabel = computed<string>(() => t("Field is required"));

  const requiredFieldRule = (val: string): boolean | string => !!val || fieldIsRequiredLabel.value;

  return { requiredFieldRule, fieldIsRequiredLabel };
}
