export interface HeaderObjectInterface {
  Accept: string;
}

export interface FetchOptionInterface {
  method: string;
  mode: string;
  headers: HeaderObjectInterface;
  body: string;
}
