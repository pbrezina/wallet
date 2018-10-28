import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';

import { AppComponent } from './app.component';
import { WalletComponent } from './wallet/wallet.component';
import { WalletService } from 'src/services/wallet.service';

@NgModule({
  declarations: [
    AppComponent,
    WalletComponent
  ],
  imports: [
    BrowserModule,
    HttpClientModule
  ],
  providers: [WalletService],
  bootstrap: [AppComponent]
})
export class AppModule { }
