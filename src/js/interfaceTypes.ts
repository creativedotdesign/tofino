export interface ContactFormProps {
  label: string;
  id: string;
  error: string;
  modelValue: string;
}

// Interface for WebFontLoader
export interface WebFontInterface {
  classes: boolean;
  events: boolean;
  google: {
    families: string[];
    display: string;
    version: number;
  };
}

// Interface for Emit Event
export interface EmitUpdateValue {
  updateValue: {
    value: string;
  };
}
