package com.ptren.absensi;

import android.os.Bundle;
import android.provider.Settings;
import android.app.AlertDialog;
import android.content.DialogInterface;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    }

    @Override
    public void onResume() {
        super.onResume();
        checkSecurity();
    }

    private void checkSecurity() {
        int devOptions = Settings.Global.getInt(this.getContentResolver(), Settings.Global.DEVELOPMENT_SETTINGS_ENABLED, 0);
        if (devOptions == 1) {
            new AlertDialog.Builder(this)
                .setTitle("Akses Ditolak (Keamanan)")
                .setMessage("Opsi Pengembang (Developer Options) terdeteksi AKTIF di HP Anda.\n\nSistem mengindikasi kemungkinan adanya penggunaan Fake GPS. Harap matikan Opsi Pengembang di Pengaturan HP Anda untuk dapat melanjutkan absensi.")
                .setCancelable(false)
                .setPositiveButton("Tutup Aplikasi", new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int id) {
                        finishAffinity();
                    }
                })
                .show();
        }
    }
}
