import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { BankAccount } from '@app/_models';

const baseUrl = `${environment.apiUrl}/bank_account`;

@Injectable({ providedIn: 'root' })
export class BankAccountService {
    constructor(private http: HttpClient) { }

    getAll() {
        return this.http.get<BankAccount[]>(baseUrl);
    }

}