export class Transaction {
  public constructor(
    /* Identify transaction */
    public name: string,
    public account: string,
    public type: string,
    public description: string,
    public note: string,

    /* Symbols */
    public variableSymbol: number,
    public constantSymbol: number,
    public specificSymbol: number,

    /* Other information */
    public createdOn: Date,
    public executedOn: Date,

    /* Ammount */
    public ammount: number
  ) {
    /* Do nothing. */
  }
}
