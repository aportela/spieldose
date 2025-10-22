import { computed } from "vue";
import { useI18n } from "vue-i18n";

export function useFormUtils() {
  const { t } = useI18n();

  const requiredFieldRules = [(val) => !!val || fieldIsRequiredLabel.value];

  const fieldIsRequiredLabel = computed(() => t("Field is required"));

  return { requiredFieldRules, fieldIsRequiredLabel };
}
