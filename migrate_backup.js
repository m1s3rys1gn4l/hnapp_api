#!/usr/bin/env node

/**
 * Converts offline Hive JSON backup to Laravel API format
 * Usage: node migrate_backup.js input.json output.json
 */

import fs from 'fs';
import path from 'path';
import { randomUUID } from 'crypto';

function uuidv4() {
  return randomUUID();
}

function migrateData(oldData) {
  const idMap = {}; // Map old IDs to new UUIDs

  // Helper to get or create UUID for ID
  const getUuid = (oldId) => {
    const key = String(oldId);
    if (!idMap[key]) {
      idMap[key] = uuidv4();
    }
    return idMap[key];
  };

  // Helper to format timestamp
  const formatTimestamp = (timestamp) => {
    try {
      const dt = new Date(timestamp);
      return dt.toISOString();
    } catch (e) {
      return new Date().toISOString();
    }
  };

  // Helper to extract date (YYYY-MM-DD)
  const extractDate = (timestamp) => {
    try {
      const dt = new Date(timestamp);
      return dt.toISOString().split('T')[0];
    } catch (e) {
      return new Date().toISOString().split('T')[0];
    }
  };

  // Migrate books
  const books = (oldData.books || []).map(book => ({
    id: getUuid(book.id),
    name: book.name,
    is_pinned: book.isPinned ?? false,
    default_client_id: book.defaultClientId ? getUuid(book.defaultClientId) : null,
    created_at: formatTimestamp(book.createdAt),
    updated_at: formatTimestamp(book.createdAt),
  }));

  // Migrate clients
  const clients = (oldData.clients || []).map(client => ({
    id: getUuid(client.id),
    name: client.name,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }));

  // Migrate transactions
  const transactions = (oldData.transactions || []).map(tx => ({
    id: getUuid(tx.id),
    book_id: getUuid(tx.bookId),
    client_id: tx.clientId ? getUuid(tx.clientId) : null,
    type: tx.type,
    amount: parseFloat(tx.amount),
    note: tx.note ?? '',
    category: tx.category ?? 'General',
    date: extractDate(tx.date),
    created_at: formatTimestamp(tx.date),
    updated_at: formatTimestamp(tx.date),
  }));

  return { books, clients, transactions };
}

// Main execution
const inputFile = process.argv[2] || 'backup.json';
const outputFile = process.argv[3] || 'backup-migrated.json';

try {
  console.log(`📖 Reading: ${inputFile}`);
  const rawData = fs.readFileSync(inputFile, 'utf8');
  const oldData = JSON.parse(rawData);

  console.log(`📝 Migrating data...`);
  const newData = migrateData(oldData);

  console.log(`📊 Statistics:`);
  console.log(`   Books: ${newData.books.length}`);
  console.log(`   Clients: ${newData.clients.length}`);
  console.log(`   Transactions: ${newData.transactions.length}`);

  fs.writeFileSync(outputFile, JSON.stringify(newData, null, 2));
  console.log(`✅ Saved to: ${outputFile}`);
  console.log(`\n🚀 Next steps:`);
  console.log(`1. Use this migrated JSON to populate the database`);
  console.log(`2. Or import it via a Laravel seeder`);
} catch (error) {
  console.error(`❌ Error: ${error.message}`);
  process.exit(1);
}
