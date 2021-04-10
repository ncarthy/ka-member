import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { TransactionSummary } from '@app/_models';

const baseUrl = `${environment.apiUrl}/transactions`;

@Injectable({ providedIn: 'root' })
export class TransactionService {
    constructor(private http: HttpClient) { }

    getAll() {
        return this.http.get<TransactionSummary[]>(`${baseUrl}/summary`);
    }

}