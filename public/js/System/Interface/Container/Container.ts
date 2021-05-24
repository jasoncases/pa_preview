export interface Container {
  init(): void;
  registerAll(): void;
  registerUi(): void;
}
