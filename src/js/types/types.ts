// Interface for WebFontLoader
export interface WebFontInterface {
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

export interface Scripts extends Array<Script> {}

// Define Script interface
export interface Script {
  selector: string;
  src: string;
  type: 'vue' | 'ts';
}
