interface TofinoJS {
  ajaxUrl: string;
  nextNonce: string;
  themeUrl: string;
  siteURL: string;
  language?: string;
  graphqlEndpoint?: string;
  iframeResizerLicense?: string;
}

declare const tofinoJS: TofinoJS;

interface AcfField {
  on(event: string, callback: () => void): void;
  val(): string;
  $layouts(): JQuery<HTMLElement>;
  add(options: { layout: string }): void;
  showNotice(options: { text: string; type: string; dismiss: boolean }): void;
  removeNotice(): void;
}

interface AcfInstance {
  getFields(args: { name: string }): AcfField[];
  getField(key: string): AcfField | undefined;
}

declare const acf: AcfInstance;
