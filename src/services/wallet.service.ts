import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { HttpClient } from '@angular/common/http';

import { Transaction } from 'src/models/wallet/transaction';

@Injectable({
  providedIn: 'root'
})
export class WalletService {
  public transactions: Transaction[];

  public constructor(private http: HttpClient) {
    this.transactions = [
      new Transaction('name1', 'account', 'type', 'description', 'not', 1, 2, 3, new Date(), new Date(), 4),
      new Transaction('name2', 'account', 'type', 'description', 'not', 1, 2, 3, new Date(), new Date(), 4)
    ];
  }

  public getTest() {
    return this.http.post('backend/rpc.php', {
      'protocol': 1,
      'type': 'request',
      'module': 'test',
      'method': 'test',
      'version': 1,
      'data': {}
    });
  }

  public getTransactions(): Observable<Transaction[]> {
    return of(this.transactions);
  }
}
