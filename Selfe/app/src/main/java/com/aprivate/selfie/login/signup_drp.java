package com.aprivate.selfie.login;

import android.os.Bundle;
import android.support.v7.widget.Toolbar;
import android.util.Patterns;
import android.view.MenuItem;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import com.aprivate.selfie.R;
import com.aprivate.selfie.wedget.custom.AbstractActivity;
import com.aprivate.selfie.wedget.custom.ProgressGenerator;
import com.dd.processbutton.iml.ActionProcessButton;
import com.github.ppamorim.dragger.DraggerPosition;
import com.github.ppamorim.dragger.DraggerView;

import butterknife.Bind;

public class signup_drp extends AbstractActivity {
    public static final String DRAG_POSITION = "drag_position";
    ProgressGenerator progressGenerator = null;
    private boolean sex = true;
    int translateY;


    @Bind(R.id.toolbar)
    Toolbar toolbar;
    @Bind(R.id.dragger_view)
    DraggerView draggerView;

    @Bind(R.id.txtName)
    EditText _nameText;

    @Bind(R.id.txtEmail)
    EditText _emailText;

    @Bind(R.id.txtPhone)
    EditText _Phonenumber;

    @Bind(R.id.imgMan)
    ImageView _SexBoy;

    @Bind(R.id.imgGirl)
    ImageView _SexGirl;

    @Bind(R.id.btn_signup)
    ActionProcessButton _signupButton;

    @Bind(R.id.link_login)
    TextView _loginLink;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        configToolbar();
        configIntents();
        progressGenerator = new ProgressGenerator();
        _SexBoy.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                sex = true;
            }
        });

        _SexGirl.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                sex = false;
            }
        });

        _signupButton.setMode(ActionProcessButton.Mode.ENDLESS);
        _signupButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                signup();
            }
        });

        _loginLink.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // Finish the registration screen and return to the Login activity
                draggerView.closeActivity();
            }
        });
    }

    @Override
    protected int getContentViewId() {
        return R.layout.activity_signup_drp;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case android.R.id.home:
                onBackPressed();
                return true;
            default:
                return super.onOptionsItemSelected(item);
        }
    }

    @Override
    public void onBackPressed() {
        draggerView.closeActivity();
    }

    private void configToolbar() {
        setSupportActionBar(toolbar);
        toolbar.setTitle(getResources().getString(R.string.app_name));
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
    }

    private void configIntents() {
        draggerView.setDraggerPosition((DraggerPosition) getIntent().getSerializableExtra(DRAG_POSITION));
    }

    public void signup() {
        ButtonSiginStatus(true);
        if (!validate()) {
            ButtonSiginStatus(false);
            onSignupFailed();
            return;
        }


//        final ProgressDialog progressDialog = new ProgressDialog(SignupActivity.this, R.style.Selfie);
//        progressDialog.setIndeterminate(true);
//        progressDialog.setMessage("Creating Account...");
//        progressDialog.show();

        final String name = _nameText.getText().toString();
        final String email = _emailText.getText().toString();
        final String phone = _Phonenumber.getText().toString();


        // TODO: Implement your own signup logic here.

        new android.os.Handler().postDelayed(
                new Runnable() {
                    public void run() {
                        // On complete call either onSignupSuccess or onSignupFailed
                        // depending on success
                        onSignupSuccess(name, email);
                        // onSignupFailed();
//                        progressDialog.dismiss();
                    }
                }, 3000);
    }

    public void onSignupSuccess(String Name, String Email) {
        ButtonSiginStatus(false);
        setResult(RESULT_OK, null);
        finish();
    }

    public void onSignupFailed() {
        Toast.makeText(getBaseContext(), "Login failed", Toast.LENGTH_LONG).show();
        ButtonSiginStatus(false);
    }

    public boolean validate() {
        boolean valid = true;

        String name = _nameText.getText().toString();
        String email = _emailText.getText().toString();
        String phone = _Phonenumber.getText().toString();

        if (name.isEmpty() || name.length() < 3) {
            _nameText.setError("at least 3 characters");
            valid = false;
        } else {
            _nameText.setError(null);
        }

        if (email.isEmpty() || !android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            _emailText.setError("enter a valid email address");
            valid = false;
        } else {
            _emailText.setError(null);
        }

        if (phone.isEmpty() || !Patterns.PHONE.matcher(phone).matches()) {
            _Phonenumber.setError("enter a valid phone number");
            valid = false;
        } else {
            _Phonenumber.setError(null);
        }

        return valid;
    }

    private void ButtonSiginStatus(boolean mood) {
        if (mood) {
            _signupButton.setText(getString(R.string.Create_Account));
            _signupButton.setProgress(0);
            progressGenerator.start(_signupButton);
            _signupButton.setEnabled(false);

        } else {
            _signupButton.setErrorText(getString(R.string.Failed));
            _signupButton.setProgress(-1);
            progressGenerator.Error(_signupButton);
            _signupButton.setEnabled(true);
        }
    }
}
