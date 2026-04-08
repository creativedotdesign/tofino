export interface WebFontConfig {
  classes: boolean;
  events: boolean;
  google?: {
    families: string[];
    display: string;
    version: number;
  };
  typekit?: {
    id: string;
  };
}

export interface Script {
  selector: string;
  src: string;
  type: 'vue' | 'ts';
}
