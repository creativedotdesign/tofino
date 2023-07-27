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
