import { Component, OnInit } from '@angular/core';
import { Transaction } from 'src/models/wallet/transaction';
import { WalletService } from 'src/services/wallet.service';

@Component({
  selector: 'wallet',
  templateUrl: './wallet.component.html'
})
export class WalletComponent implements OnInit {
  private transactions: Transaction[];
  private test;

  constructor(private wallet: WalletService) {
    /* Nothig to do. */
  }

  ngOnInit() {
    this.wallet.getTransactions().subscribe(
      transactions => this.transactions = transactions
    );

    this.wallet.getTest().subscribe(
      output => this.test = output
    );
  }
}
